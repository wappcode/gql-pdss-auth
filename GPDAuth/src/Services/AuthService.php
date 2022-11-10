<?php

namespace GPDAuth\Services;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Exception;
use GPDAuth\Entities\Permission;
use GPDAuth\Entities\User;
use GPDAuth\Library\IAuthService;
use GPDAuth\Library\PasswordManager;

@session_start();
class AuthService implements IAuthService
{

    const AUTH_SESSION_ID = 'gpd_auth_session_id';
    /**
     * @var array
     */
    protected $user;
    /**
     *
     * @var array
     */
    protected $permissions;
    /**
     * @var EntityManager
     */
    protected $entityManager;


    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }




    /**
     *
     * @param string $username
     * @param string $password
     * @return void
     * @throws Exception
     */
    public function login(string $username, string $password)
    {
        /** @todo 
         * create JWT
         * set JWT on headers
         */
        $user = $this->findUser($username);
        if (empty($user)) {
            throw new Exception('Invalid user');
        }
        $userPassword = $user["password"] ?? '';
        $salt = $user["salt"] ?? '';
        $algorithm = $user['algorithm'] ?? null;
        $encodedPassword = PasswordManager::encode($password, $salt, $algorithm);
        if ($encodedPassword !== $userPassword) {
            throw new Exception('Invalid user');
        }
        $_SESSION[static::AUTH_SESSION_ID] = $user->getUsername();
        return true;
    }
    /**
     * @return void
     */
    public function logout(): void
    {
        $_SESSION[static::AUTH_SESSION_ID] = null;
    }

    public function getUser(): array
    {
        if (empty($this->user)) {

            /**
             * @todo 
             * Obtener el username por encabezado o por sesion
             */
            $username = $this->getSessionAuthId();
            $this->user = $this->findUser($username);
        }
        return $this->user;
    }

    public function hasRole(string $role): bool
    {
        $roles = $this->getRoles();
        return in_array($role, $roles);
    }
    public function hasSomeRoles(array $roles): bool
    {
        $userRoles = $this->getRoles();
        $intersect = array_intersect($userRoles, $roles);
        return count($intersect) > 0;
    }
    public function hasAllRole(array $roles): bool
    {
        $userRoles = $this->getRoles();
        $intersect = array_intersect($userRoles, $roles);
        $intersectUnique = array_unique($intersect);
        return count($intersect) == count($intersectUnique);
        return true;
    }
    public function hasPermission(string $resource, string $permissionValue, ?string $scope = null): bool
    {
        $permission = $this->findPermission($resource, $permissionValue, $scope);
        $permissionAccess = $permission["access"] ?? Permission::DENY;
        return $permissionAccess === Permission::ALLOW;
    }
    public function hasSomePermissions(array $resources, array $permissionsValues, ?array $scopes = null): bool
    {
        $result = false;
        foreach ($resources as $resource) {
            foreach ($permissionsValues as $permissionValue) {
                if (empty($scopes)) {
                    $flag = $this->hasPermission($resource, $permissionValue);
                    if ($flag === true) {
                        $result = true;
                        break 2;
                    }
                }
                foreach ($scopes as $scope) {
                    $flag = $this->hasPermission($resource, $permissionValue, $scope);
                    if ($flag === true) {
                        $result = true;
                        break 3;
                    }
                }
            }
        }
        return $result;
    }
    public function hasAllPermissions(array $resources, array $permissionsValues, ?array $scopes = null): bool
    {
        if (empty($resources) || empty($permissionsValues)) {
            return false;
        }
        $result = true;
        foreach ($resources as $resource) {
            foreach ($permissionsValues as $permissionValue) {
                if (empty($scopes)) {
                    $flag = $this->hasPermission($resource, $permissionValue);
                    if ($flag === false) {
                        $result = false;
                        break 2;
                    }
                }
                foreach ($scopes as $scope) {
                    $flag = $this->hasPermission($resource, $permissionValue, $scope);
                    if ($flag === false) {
                        $result = false;
                        break 3;
                    }
                }
            }
        }
        return $result;
    }
    public function getRoles(): array
    {
        $user = $this->getUser();
        $roles = $user["roles"];
        if (!is_array($roles)) {
            return [];
        }
        $rolesCodes = array_map(function ($role) {
            $role["code"];
        }, $roles);
        return $rolesCodes;
    }
    protected function setPermissions()
    {
    }

    protected function getPermissions(): array
    {
        if (!is_array($this->permissions)) {
            $user = $this->getUser();
            $userId = $user["id"];
            $roles = $user["roles"] ?? [["id" => "0"]];
            $rolesIds = array_map(function ($role) {
                $role["id"];
            }, $roles);
            $qb = $this->entityManager->createQueryBuilder()->from(Permission::class, 'permission')
                ->innerJoin('permission.resource', 'resource')
                ->leftJoin('permission.user', 'user')
                ->leftJoin('permission.role', 'role')
                ->select(['permission', 'partial user.{id}', 'partial role.{id, code}', 'partial resource.{id,code}']);
            $condigionRole = $qb->expr()->in('permission.role', ':rolesIds');
            $conditionUser = 'permission.user = :userId';
            $conditionGlobal = $qb->expr()->andX($qb->expr()->isNull("permission.user"), $qb->expr()->isNull("permission.role"));

            $qb->andWhere($qb->expr()->orX($conditionUser, $condigionRole, $conditionGlobal))
                ->setParameter(':rolesIds', $rolesIds)
                ->setParameter(':userId', $userId)
                ->orderBy('permission.updated', 'desc');
            $permissions = $qb->getQuery()->getArrayResult() ?? [];
            $permissions = $this->sortPermissions($permissions);
            $permissions = $this->standardizePermissions($permissions);
            $this->permissions = $permissions;
        }
        return $permissions;
    }

    /**
     * Ordena los permisos dando prioridad a usuario, roles y al final permisos globales
     * el orden es descendiente por fecha de actualización
     */
    private function sortPermissions(array $permissions): array
    {

        usort($permissions, function ($a, $b) {
            $userA = isset($a["user"]["id"]) ? -1 : 1;
            $userB = isset($b["user"]["id"]) ? -1 : 1;
            if ($userA != $userB) {
                return $userA <=> $userB;
            }
            /** @var DateTimeInterface */
            $updatedA = ($a["updated"] instanceof DateTimeInterface) ? $a["updated"] : new DateTime($a["updated"]);
            /** @var DateTimeInterface */
            $updatedB = ($b["updated"] instanceof DateTimeInterface) ? $b["updated"] : new DateTime($b["updated"]);
            $timeA = $updatedA->getTimestamp();
            $timeB = $updatedB->getTimestamp();
            return $timeB <=> $timeA; // orden descendente por fecha

        });
        return $permissions;
    }

    private function standardizePermissions(array $permissions): array
    {
        $standardizedPermissions = array_map(function ($permission) {
            $permission["resource"] = $permission["resource"]["code"];
            $permission["role"] = $permission["role"]["code"];
        }, $permissions);
        return $standardizedPermissions;
    }

    protected function findPermission(string $resource, string $permissionValue, ?string $scope): ?array
    {
        $result = null;
        $permissions = $this->getPermissions();
        foreach ($permissions as $permission) {
            if ($resource != $permission["resource"] || $permissionValue != $permission["value"]) continue;
            if ($scope === null || $scope == $permission["scope"]) {
                $result = $permission;
                break;
            }
        }
        return $result;
    }
    protected function findUser(string $username)
    {
        $qb = $this->entityManager->createQueryBuilder()->from(User::class, 'user')
            ->leftJoin('user.roles', 'roles')
            ->select(['user', 'roles']);
        $qb->andWhere('user.username = :username')
            ->setParameter(':username', $username);
        $user = $qb->getQuery()->getOneOrNullResult(Query::HYDRATE_ARRAY);
        return $user;
    }
    public function getSessionAuthId(): ?string
    {
        return $_SESSION[static::AUTH_SESSION_ID] ?? null;
    }
}
