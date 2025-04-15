<?php

namespace GPDAuth\Services;

use DateTime;
use Exception;
use GPDAuth\Entities\Role;
use GPDAuth\Entities\User;
use Doctrine\ORM\EntityManager;
use GPDAuth\Entities\Permission;
use GPDAuth\Library\IAuthService;
use GPDAuth\Library\PasswordManager;
use GPDAuth\Library\InvalidUserException;
use GPDAuth\Models\AuthSessionPermission;

@session_start();
class AuthService extends AbstractAuthService
{



    /**
     * @var EntityManager
     */
    protected $entityManager;

    public function __construct(
        EntityManager $entityManager,
        string $iss,
        string $authMethod = IAuthService::AUTHENTICATION_METHOD_SESSION,
        ?string $jwtSecureKey = null,
        array $issuersConfig = []
    ) {
        parent::__construct($iss, $authMethod, $jwtSecureKey, $issuersConfig);
        $this->entityManager = $entityManager;
    }
    /**
     *
     * 
     * Hay que inicializar sesion, roles y permisos con sus respectivos metodos setSession, setRoles y setPermissions
     * 
     * @param string $username
     * @param string $password
     * @throws Exception
     */
    public function login(string $username, string $password): void
    {

        $user = $this->findUser($username);
        if (!$this->validUser($password, $user)) {
            throw new InvalidUserException('Invalid username and password');
        }
        $session = $this->userToSession($user);
        $roles = $session["roles"] ?? [];
        $username = $this->getUsernameFromSessionData($session);
        $permissions = $this->getPermissionsFromDB($roles, $username);
        $this->setSession($session);
        $this->setRoles($roles);
        $this->setPermissions($permissions);
    }

    /**
     * Sobreescribir este método para hacer un login personalizado
     * Si se usan permisos hay que inicializar valor de la propiedad permissions con un array de AuthSessionPermission
     *
     * @return void
     */
    protected function loginJWT(): void
    {
        $jwtData = $this->getSessionFromJWT();
        if (empty($jwtData)) {
            return;
        }
        $session = $jwtData;
        $username = $this->getUsernameFromSessionData($session);
        $requestIss = $session["iss"] ?? null;
        $permissions = [];
        try {
            // busca al usuario siempre y cuando se el mismo idprovider
            if ($requestIss === $this->getISS()) {
                if (empty($username)) {
                    throw new Exception("Sub value is required");
                }
                $user = $this->findUser($username);
                if ($user instanceof User) {
                    if (!$user->getActive()) {
                        throw new Exception("Inactive user");
                    }
                    $session = $this->userToSession($user);
                    $permissions = $this->getPermissionsFromDB($session["roles"], $username);
                }
            } elseif (!empty($requestIss)) {
                // Filtra los roles permitidos para el issue
                $session["roles"] = $this->filterIssRoles($requestIss, $session["roles"] ?? []);
            }

            $roles = $session["roles"] ?? [];
            $this->setSession($session);
            $this->setRoles($roles);
            $this->setPermissions($permissions);
        } catch (Exception $e) {
            $this->clearSession();
        }
    }

    /**
     * Sobreescribir este método para hacer un login personalizado
     * Si se usan permisos hay que inicializar valor de la propiedad permissions con un array de AuthSessionPermission
     *
     * @return void
     */
    protected function loginSession()
    {
        $username = $this->getUsernameFromPHPSession();
        if (empty($username)) {
            return;
        }
        $user = $this->findUser($username);
        if (!($user instanceof User)) {
            return;
        }
        $session = $this->userToSession($user);
        $roles = $session["roles"] ?? [];
        $username = $this->getUsernameFromSessionData($session);
        $permissions = $this->getPermissionsFromDB($roles, $username);
        $this->setSession($session);
        $this->setRoles($roles);
        $this->setPermissions($permissions);
    }



    protected function validUser(string $password, ?User $user): bool
    {
        if (!($user instanceof User)) {
            return false;
        }
        $userPassword = $user->getPassword();
        $salt = $user->getSalt();
        $algorithm = $user->getAlgorithm();
        $encodedPassword = PasswordManager::encode($password, $salt, $algorithm);
        if ($encodedPassword !== $userPassword) {
            return false;
        }
        return true;
    }
    /**
     * Gets the user permissions from database
     * Establece los permisos del usuario obtenidos desde los registros de la base de datos
     * Esta función debe llamarse despues de setSession o clearSession ya que estos métodos limpia todos los datos de la sesión y los permisos
     * @param array $rolesCodes [string]
     * @param ?string $username 
     * @return array  Permission as array
     */
    protected function getPermissionsFromDB(array $rolesCodes, ?string $username = null): array
    {
        if (!is_array($this->permissions)) {
            $qb = $this->entityManager->createQueryBuilder()->from(Permission::class, 'permission')
                ->innerJoin('permission.resource', 'resource')
                ->leftJoin('permission.user', 'user')
                ->leftJoin('permission.role', 'role')
                ->select(['permission', 'partial user.{id}', 'partial role.{id, code}', 'partial resource.{id,code}']);
            $condigionRole = $qb->expr()->in('role.code', ':rolesCodes');
            $conditionUser = 'user.username like :username';
            $conditionGlobal = $qb->expr()->andX($qb->expr()->isNull("permission.user"), $qb->expr()->isNull("permission.role"));

            if ($username != null) {
                $qb->andWhere($qb->expr()->orX($conditionUser, $condigionRole, $conditionGlobal))
                    ->setParameter(':username', $username);
            } else {
                $qb->andWhere($qb->expr()->orX($condigionRole, $conditionGlobal));
            }
            $qb->setParameter(':rolesCodes', $rolesCodes)
                ->orderBy('permission.updated', 'desc');
            $permissions = $qb->getQuery()->getResult() ?? [];
            $permissions = $this->sortDBPermissions($permissions);
            $permissionsResult = array_map(function (Permission $permissionObj) {
                $resource = $permissionObj->getResource()->getCode();
                $access = $permissionObj->getAccess();
                $value = $permissionObj->getValue();
                $scope = $permissionObj->getScope();
                $permission = new AuthSessionPermission($resource, $access, $value, $scope);
                return $permission;
            }, $permissions);
        }
        return $permissionsResult;
    }

    /**
     * Ordena los permisos que provienen de la base de datos. Da prioridad a usuario, roles y al final permisos globales
     * el orden es descendiente por fecha de actualización
     */
    protected function sortDBPermissions(array $permissions): array
    {

        usort($permissions, function (Permission $a, Permission $b) {
            // ordena primero los permisos que son de usuario
            $userA = ($a->getUser() instanceof User) ? -1 : 1;
            $userB = ($b->getUser() instanceof User) ? -1 : 1;
            if ($userA != $userB) {
                return $userA <=> $userB;
            }
            // ordena segundo los permisos que son de roles
            $roleA = ($a->getRole() instanceof Role) ? -1 : 1;
            $roleB = ($b->getRole() instanceof Role) ? -1 : 1;
            if ($roleA != $roleB) {
                return $roleA <=> $roleB;
            }

            // ordena al final los permisos por fecha en forma descendente para darle importancia a los últimos

            $updatedA = $a->getUpdated();
            $updatedB = $b->getUpdated();
            $timeA = $updatedA->getTimestamp();
            $timeB = $updatedB->getTimestamp();
            return $timeB <=> $timeA; // orden descendente por fecha

        });
        return $permissions;
    }


    protected function findUser(string $username): ?User
    {
        $qb = $this->entityManager->createQueryBuilder()->from(User::class, 'user')
            ->leftJoin('user.roles', 'roles')
            ->select(['user', 'roles']);
        $qb->andWhere('user.username = :username')
            ->setParameter(':username', $username);
        $user = $qb->getQuery()->getOneOrNullResult();
        return $user;
    }



    /**
     * Sobreescribir este método para  agregar claims personalizados o modificar los existentes
     *
     * @return array
     */
    protected function getCustomOrModifiedClaims(): array
    {
        return [];
    }
    protected function userToSession(User $user): array
    {
        $iss = $this->getISS();
        $jwtId = sprintf("%s::%s", $iss, $user->getUsername());
        $currenttime = new DateTime();
        $expiration = new DateTime();
        $expiration->modify("+{$this->jwtExpirationTimeInSeconds} seconds");
        $roles = $this->getUserRoles($user);
        $session = [
            "auth_time" => $currenttime->getTimestamp(),
            "sub" => $user->getUsername(),
            "birth_family_name" => $user->getLastName(),
            "birth_given_name" => $user->getFirstName(),
            "email" => $user->getEmail(),
            "exi" => $this->jwtExpirationTimeInSeconds,
            "exp" => $expiration->getTimestamp(),
            "family_name" => $user->getLastName(),
            "given_name" => $user->getFirstName(),
            "iss" => $iss,
            "jti" => $jwtId,
            "name" => $user->getFirstName() . " " . $user->getLastName(),
            "picture" => $user->getPicture(),
            "preferred_username" => $user->getUsername(),
            "roles" => $roles,
        ];
        $customClaims = $this->getCustomOrModifiedClaims();

        return array_merge($session, $customClaims);
    }


    protected function getUserRoles(User $user): array
    {
        $rolesObj = $user->getRoles();
        $roles = [];
        /** @var Role */
        foreach ($rolesObj as $role) {
            $roles[] = $role->getCode();
        }
        return $roles;
    }
}
