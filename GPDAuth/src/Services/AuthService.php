<?php

namespace GPDAuth\Services;

use DateTime;
use DateTimeInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Query;
use Exception;
use GPDAuth\Entities\Permission;
use GPDAuth\Entities\User;
use GPDAuth\Library\AuthJWTManager;
use GPDAuth\Library\IAuthService;
use GPDAuth\Library\InvalidUserException;
use GPDAuth\Library\PasswordManager;

@session_start();
class AuthService implements IAuthService
{

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


    /**
     *
     * @var string
     */
    protected $jwtSecureKey;

    /**
     *
     * @var string
     */
    protected $sessionKey;

    /**
     * @var string
     */
    protected $jwtAlgoritm;

    protected $jwtDefaultExpirationTime;

    /**
     * @var ?string
     */
    protected $currentJWT;

    public function __construct(
        EntityManager $entityManager,
        string $jwtSecureKey,
        string $sessionKey,
        string $jwtAlgoritm,
        string $jwtDefaultExpirationTime
    ) {
        $this->entityManager = $entityManager;
        $this->jwtAlgoritm = $jwtAlgoritm;
        $this->sessionKey = $sessionKey;
        $this->jwtSecureKey = $jwtSecureKey;
        $this->jwtDefaultExpirationTime = $jwtDefaultExpirationTime;
    }




    /**
     *
     * @param string $username
     * @param string $password
     * @throws Exception
     */
    public function login(string $username, string $password): void
    {

        $user = $this->findUser($username);
        if (!$this->validUser($password, $user)) {
            throw new InvalidUserException();
        }
        $this->user = $user;
        $this->setUser($user);
        $_SESSION[$this->sessionKey] = $user["username"];
    }
    /**
     * @return void
     */
    public function logout(): void
    {
        $this->user = null;
        $this->roles = null;
        $this->permissions = null;
        $_SESSION[$this->sessionKey] = null;
    }

    /**
     * Realiza el login asignando directamente al usuario
     */
    public function setUser(User $user) {
        $this->logout();
        $this->user = $user;
        $this->updateJWT();
    }

    

    /**
     * Se considera que esta firmado si tiene registro de usuario
     *
     * @return boolean
     */
    public function isSigned(): bool
    {
        $user = $this->getUser();
        return (is_array($user) && !empty($user));
    }
    public function getUser(): ?array
    {
        $username = $this->getAuthId();
        if (empty($username)) {
            return null;
        }
        if (empty($this->user)) {

            $this->user = $this->findUser($username);
        }
        $this->updateJWT();
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
    public function hasAllRoles(array $roles): bool
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
        if (!is_array($user)) {
            return [];
        }
        $roles = $user["roles"];
        if (!is_array($roles)) {
            return [];
        }
        $rolesCodes = array_map(function ($role) {
            return $role["code"];
        }, $roles);
        return $rolesCodes;
    }
    public function getPermissions(): array
    {
        if (!is_array($this->permissions)) {
            $user = $this->getUser();
            if (!is_array($user)) {
                return [];
            }
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

    private function validUser(string $password, ?array $user): bool
    {
        if (empty($user)) {
            return false;
        }
        $userPassword = $user["password"] ?? '';
        $salt = $user["salt"] ?? '';
        $algorithm = $user['algorithm'] ?? null;
        $encodedPassword = PasswordManager::encode($password, $salt, $algorithm);
        if ($encodedPassword !== $userPassword) {
            return false;
        }
        return true;
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
            if ($resource != $permission["resource"] || ($permissionValue != $permission["value"] && $permission["value"] != Permission::ALL)) continue;
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
    public function getAuthId(): ?string
    {

        $jwtAuthId = $this->getAuthIdFromJWT();
        if (!empty($jwtAuthId)) {
            return $jwtAuthId;
        }
        $sessionId = $_SESSION[$this->sessionKey] ?? null;
        if (!empty($sessionId)) {
            return $sessionId;
        }
        return null;
    }

    public function getAuthIdFromJWT(): ?string
    {
        $token = AuthJWTManager::getTokenFromAuthoriaztionHeader();
        if (empty($token)) {
            return null;
        }
        $data = AuthJWTManager::getTokenData($token, $this->jwtSecureKey, $this->jwtAlgoritm);
        if (!static::validateJWTData($data)) {
            return null;
        }
        return $data["username"] ?? null;
    }

    public function validateJWTData(array $data): bool
    {
        $exp = $data["exp"] ?? null;
        if (empty($exp)) {
            return false;
        }
        $currentDate = new DateTime();
        if ($exp < $currentDate->getTimestamp()) {
            return false;
        }
        return true;
    }

    private function updateJWT(): void
    {
        $token = AuthJWTManager::createUserToken($this->user, $this->jwtSecureKey, $this->jwtDefaultExpirationTime, $this->jwtAlgoritm);
        AuthJWTManager::addTokenToResponseHeader($token);
        $this->currentJWT = $token;
    }
    public function getCurrentJWT(): ?string
    {
        return $this->currentJWT;
    }

    /**
     * Get the value of jwtAlgoritm
     *
     * @return  string
     */
    public function getJwtAlgoritm()
    {
        return $this->jwtAlgoritm;
    }

    /**
     * Set the value of jwtAlgoritm
     *
     * @param  string  $jwtAlgoritm
     *
     * @return  self
     */
    public function setJwtAlgoritm(string $jwtAlgoritm)
    {
        $this->jwtAlgoritm = $jwtAlgoritm;

        return $this;
    }

    /**
     * Get the value of sessionKey
     *
     * @return  string
     */
    public function getSessionKey()
    {
        return $this->sessionKey;
    }

    /**
     * Set the value of sessionKey
     *
     * @param  string  $sessionKey
     *
     * @return  self
     */
    public function setSessionKey(string $sessionKey)
    {
        $this->sessionKey = $sessionKey;

        return $this;
    }

    /**
     * Get the value of jwtSecureKey
     *
     * @return  string
     */
    public function getJwtSecureKey()
    {
        return $this->jwtSecureKey;
    }

    /**
     * Set the value of jwtSecureKey
     *
     * @param  string  $jwtSecureKey
     *
     * @return  self
     */
    public function setJwtSecureKey(string $jwtSecureKey)
    {
        $this->jwtSecureKey = $jwtSecureKey;

        return $this;
    }

    /**
     * Get the value of jwtDefaultExpirationTime
     */
    public function getJwtDefaultExpirationTime()
    {
        return $this->jwtDefaultExpirationTime;
    }

    /**
     * Set the value of jwtDefaultExpirationTime
     *
     * @return  self
     */
    public function setJwtDefaultExpirationTime($jwtDefaultExpirationTime)
    {
        $this->jwtDefaultExpirationTime = $jwtDefaultExpirationTime;

        return $this;
    }
}
