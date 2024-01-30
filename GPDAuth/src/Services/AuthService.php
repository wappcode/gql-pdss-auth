<?php

namespace GPDAuth\Services;

use DateTime;
use Exception;
use DateTimeInterface;
use GPDAuth\Entities\Role;
use GPDAuth\Entities\User;
use Doctrine\ORM\EntityManager;
use GPDAuth\Models\AuthSession;
use GPDAuth\Entities\Permission;
use GPDAuth\Library\IAuthService;
use GPDAuth\Library\AuthJWTManager;
use GPDAuth\Library\PasswordManager;
use GPDAuth\Library\InvalidUserException;
use GPDAuth\Models\AuthSessionPermission;
use GPDAuth\Models\AuthSessionUser;

@session_start();
class AuthService implements IAuthService
{

    /**
     * @var ?AuthSession
     */
    protected $session;

    /**
     * Usuario de la sesión
     *
     * @var ?AuthSessionUser
     */
    protected $user;
    /**
     *
     * @var array
     */
    protected $roles;
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

    /**
     * Seconds to expire jwt
     *
     * @var int
     */
    protected $jwtExpirationTimeInSeconds;

    protected $authMethod;

    /**
     * Nuevo JWT que se utilizara como respuesta de la solicitud
     *
     * @var ?string
     */
    protected $newJWT = null;

    protected $iss = null;


    public function __construct(
        EntityManager $entityManager,
        string $iss,
        string $authMethod = IAuthService::AUTHENTICATION_METHOD_SESSION,
        ?string $jwtSecureKey = null
    ) {
        $this->entityManager = $entityManager;
        $this->jwtAlgoritm = "HS256";
        $this->sessionKey = "gpdauth_session_id";
        $this->jwtSecureKey = $jwtSecureKey;
        $this->jwtExpirationTimeInSeconds = 1200; // 20 minutos
        $this->authMethod = $authMethod;
        $this->iss = $iss;
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
            throw new InvalidUserException('Invalid username and password');
        }
        $session = $this->userToSession($user);
        $roles = $session->getRoles() ?? [];
        $this->setSession($session);
        $this->setPermissionsFromDB($roles, $user->getId());
    }

    /**
     * @return void
     */
    public function logout(): void
    {
        $this->clearSession();
        $_SESSION[$this->sessionKey] = null;
        AuthJWTManager::addJWTToHeader("");
    }
    /**
     * Se considera que esta firmado si tiene registro de usuario
     *
     * @return boolean
     */
    public function isSigned(): bool
    {
        $session = $this->getSession();
        return ($session instanceof AuthSession && !empty($session->getSub()));
    }
    public function getSession(): ?AuthSession
    {
        return $this->session;
    }
    public function getUser(): ?AuthSessionUser
    {
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
        $permissionAccess = ($permission instanceof AuthSessionPermission) ? $permission->getAccess() : Permission::DENY;
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
        return $this->getSession()->getRoles() ?? [];
    }
    /**
     * Establece los roles
     *
     * @param array $roles
     * @return AuthService
     */
    public function setRoles(array $roles): AuthService
    {
        $this->roles = $roles;
        return $this;
    }
    public function getPermissions(): array
    {
        return $this->permissions ?? [];
    }
    /**
     * Establece los permisos
     *
     * @param array $permissions [AuthSessionPermission]
     * @return AuthService
     */
    public function setPermissions(array $permissions): AuthService
    {
        $this->permissions = $permissions;
        return $this;
    }

    public function getAuthId(): ?string
    {
        $session = $this->getSession();
        return ($session instanceof AuthSession) ? $session->getSub() : null;
    }
    /**
     * Get nuevo JWT que se utilizara como respuesta de la solicitud
     *
     * @return  ?string
     */
    public function getNewJWT(): ?string
    {
        return $this->newJWT;
    }


    /**
     * Get the value of jwtAlgoritm
     *
     * @return  string
     */
    public function getJwtAlgoritm(): string
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
    public function setJwtAlgoritm(string $jwtAlgoritm): AuthService
    {
        $this->jwtAlgoritm = $jwtAlgoritm;

        return $this;
    }

    /**
     * Get the value of sessionKey
     *
     * @return  string
     */
    public function getSessionKey(): string
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
    public function setSessionKey(string $sessionKey): AuthService
    {
        $this->sessionKey = $sessionKey;

        return $this;
    }

    /**
     * Get the value of jwtSecureKey
     *
     * @return  string
     */
    public function getJwtSecureKey(): ?string
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
    public function setJwtSecureKey(?string $jwtSecureKey): AuthService
    {
        $this->jwtSecureKey = $jwtSecureKey;

        return $this;
    }

    /**
     * Get the value of jwtExpirationTimeInSeconds
     */
    public function getjwtExpirationTimeInSeconds(): int
    {
        return $this->jwtExpirationTimeInSeconds;
    }

    /**
     * Set the value of jwtExpirationTimeInSeconds
     *
     * @return  self
     */
    public function setjwtExpirationTimeInSeconds(int $jwtExpirationTimeInSeconds): AuthService
    {
        $this->jwtExpirationTimeInSeconds = $jwtExpirationTimeInSeconds;

        return $this;
    }


    /**
     * Inicializa los datos de la sesión objtenidos ya se de sesion php o de jwt
     *
     * @return void
     */
    public function initSession()
    {
        if ($this->authMethod == IAuthService::AUTHENTICATION_METHOD_JWT) {
            $this->loginJWT();
        }
        if ($this->authMethod == IAuthService::AUTHENTICATION_METHOD_SESSION) {
            $this->loginSession();
        }
        if ($this->authMethod == IAuthService::AUTHENTICATION_METHOD_SESSION_OR_JWT) {
            $this->loginSession();
            if (!($this->session instanceof AuthSession)) {
                $this->loginJWT();
            }
        }
        if ($this->authMethod == IAuthService::AUTHENTICATION_METHOD_JWT_OR_SESSION) {
            $this->loginJWT();
            if (!($this->session instanceof AuthSession)) {
                $this->loginSession();
            }
        }
    }

    protected function loginJWT()
    {
        $jwt = AuthJWTManager::retriveJWT();
        if (empty($jwt)) {
            return;
        }
        if (empty($this->jwtAlgoritm) || empty($this->jwtSecureKey)) {
            throw new Exception("Invalid JWT configuration. SecureKey or Algoritm are missing");
        }
        try {
            $jwtData = AuthJWTManager::getJWTData($jwt, $this->jwtSecureKey, $this->jwtAlgoritm);
            if (empty($jwtData)) {
                return;
            }
            $session = new AuthSession();
            $session->fillFromArray($jwtData);
            $userId = null;
            // busca al usuario siempre y cuando se el mismo idprovider
            if ($session->getIss() === $this->getISS()) {
                $sub = $session->getSub();
                if (empty($sub)) {
                    throw new Exception("Sub value is required");
                }
                $user = $this->findUser($session->getSub());
                $userId = ($user instanceof User) ? $user->getId() : null;
            }
            $roles = $session->getRoles() ?? [];
            $this->setSession($session);
            $this->setPermissionsFromDB($roles, $userId);
        } catch (Exception $e) {
            $this->clearSession();
        }
    }

    protected function loginSession()
    {
        $username = $_SESSION[$this->sessionKey] ?? null;
        if (empty($username)) {
            return;
        }
        $user = $this->findUser($username);
        if (!($user instanceof User)) {
            return;
        }
        $session = $this->userToSession($user);
        $roles = $session->getRoles() ?? [];
        $this->setSession($session);
        $this->setPermissionsFromDB($roles, $user->getId());
    }

    protected function clearSession(): void
    {
        $this->session = null;
        $this->permissions = null;
        $this->roles = null;
        $this->newJWT = null;
        $this->user = null;
    }


    /**
     * Realiza el login asignando directamente al usuario
     */
    public function setSession(AuthSession $session): AuthService
    {
        $this->clearSession();
        $this->session = $session;
        $this->user = $this->sessionToUser($this->session);
        if ($this->authMethod == IAuthService::AUTHENTICATION_METHOD_JWT || $this->authMethod == IAuthService::AUTHENTICATION_METHOD_JWT_OR_SESSION || $this->authMethod == IAuthService::AUTHENTICATION_METHOD_SESSION_OR_JWT) {
            $this->updateJWT();
        }
        if ($this->authMethod == IAuthService::AUTHENTICATION_METHOD_SESSION || $this->authMethod == IAuthService::AUTHENTICATION_METHOD_JWT_OR_SESSION || $this->authMethod == IAuthService::AUTHENTICATION_METHOD_SESSION_OR_JWT) {
            $_SESSION[$this->sessionKey] = $session->getSub();
        }
        return $this;
    }


    /**
     * Sets the user permissions from database
     * Establece los permisos del usuario obtenidos desde los registros de la base de datos
     * Esta función debe llamarse despues de setSession o clearSession ya que estos métodos limpia todos los datos de la sesión y los permisos
     * @param array $rolesCodes [string]
     * @param mixed $userId int|string
     * @return array  Permission as array
     */
    protected function setPermissionsFromDB(array $rolesCodes, $userId = null): array
    {
        if (!is_array($this->permissions)) {
            $qb = $this->entityManager->createQueryBuilder()->from(Permission::class, 'permission')
                ->innerJoin('permission.resource', 'resource')
                ->leftJoin('permission.user', 'user')
                ->leftJoin('permission.role', 'role')
                ->select(['permission', 'partial user.{id}', 'partial role.{id, code}', 'partial resource.{id,code}']);
            $condigionRole = $qb->expr()->in('role.code', ':rolesCodes');
            $conditionUser = 'permission.user = :userId';
            $conditionGlobal = $qb->expr()->andX($qb->expr()->isNull("permission.user"), $qb->expr()->isNull("permission.role"));

            if ($userId != null) {
                $qb->andWhere($qb->expr()->orX($conditionUser, $condigionRole, $conditionGlobal))
                    ->setParameter(':userId', $userId);
            } else {
                $qb->andWhere($qb->expr()->orX($$condigionRole, $conditionGlobal));
            }
            $qb->setParameter(':rolesCodes', $rolesCodes)
                ->orderBy('permission.updated', 'desc');
            $permissions = $qb->getQuery()->getResult() ?? [];
            $permissions = $this->sortDBPermissions($permissions);
            $this->permissions = array_map(function (Permission $permissionObj) {
                $resource = $permissionObj->getResource()->getCode();
                $access = $permissionObj->getAccess();
                $value = $permissionObj->getValue();
                $scope = $permissionObj->getScope();
                $permission = new AuthSessionPermission($resource, $access, $value, $scope);
                return $permission;
            }, $permissions);
        }
        return $this->permissions;
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

    /**
     * Localiza un determinado permiso
     *
     * @param string $resource
     * @param string $permissionValue
     * @param string|null $scope
     * @return AuthSessionPermission|null
     */
    protected function findPermission(string $resource, string $permissionValue, ?string $scope): ?AuthSessionPermission
    {
        $result = null;
        $permissions = $this->getPermissions();
        /** @var AuthSessionPermission */
        foreach ($permissions as $permission) {
            if ($resource != $permission->getResource() || ($permissionValue != $permission->getValue() && $permission->getValue() != Permission::ALL)) continue;
            if ($scope === null || $scope == $permission->getScope()) {
                $result = $permission;
                break;
            }
        }
        return $result;
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

    protected function updateJWT(): void
    {
        if (!$this->session) {
            return;
        }
        $session = clone $this->session;
        $session->setIat(new DateTime());
        $token = AuthJWTManager::createToken($session, $this->jwtSecureKey, $this->jwtAlgoritm);
        AuthJWTManager::addJWTToHeader($token);
        $this->newJWT = $token;
    }


    protected function userToSession(User $user): AuthSession
    {
        $iss = $this->getISS();
        $jwtId = sprintf("%s::%s", $iss, $user->getUsername());
        $currenttime = new DateTime();
        $expiration = new DateTime();
        $expiration->modify("+{$this->jwtExpirationTimeInSeconds} seconds");
        $session = new AuthSession();
        $roles = $this->getUserRoles($user);
        $session->setAuth_time($currenttime)
            ->setSub($user->getUsername())
            ->setBirth_family_name($user->getLastName())
            ->setBirth_given_name($user->getFirstName())
            ->setEmail($user->getEmail())
            ->setExi($this->jwtExpirationTimeInSeconds)
            ->setExp($expiration)
            ->setFamily_name($user->getLastName())
            ->setGiven_name($user->getFirstName())
            ->setIss($iss)
            ->setJti($jwtId)
            ->setName($user->getFirstName() . " " . $user->getLastName())
            ->setPicture($user->getPicture())
            ->setPreferred_username($user->getUsername())
            ->setRoles($roles);
        return $session;
    }
    protected function getISS()
    {
        return $this->iss;
    }

    protected function getUserRoles(User $user): array
    {
        $rolesObj = $user->getRoles();
        $roles = [];
        /** @var Role */
        foreach ($roles as $role) {
            $roles[] = $role->getCode();
        }
        return $roles;
    }
    protected function sessionToUser(?AuthSession $session): ?AuthSessionUser
    {
        if (!($session instanceof AuthSession)) {
            return null;
        }
        $user = new AuthSessionUser();
        $user->setFullName($session->getName())
            ->setFirstName($session->getGiven_name())
            ->setLastName($session->getFamily_name())
            ->setEmail($session->getEmail())
            ->setPicture($session->getPicture())
            ->setUsername($session->getSub());
        return $user;
    }
}
