<?php

namespace GPDAuth\Services;

use DateTime;
use Exception;
use GPDAuth\Entities\Permission;
use GPDAuth\Library\AuthServiceInterface;
use GPDAuth\Library\AuthMethod;
use GPDAuth\Library\AuthConfigKey;
use GPDAuth\Library\JwtAlgorithm;
use GPDAuth\Library\AuthJWTManager;
use GPDAuth\Models\AuthSessionPermission;
use GPDAuth\Models\AuthSessionUser;

@session_start();
abstract class AbstractAuthService implements AuthServiceInterface
{

    /**
     * @var ?array
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
     * @var ?array string[]
     */
    protected $roles;
    /**
     *
     * @var ?array AuthSessionPermission[]
     */
    protected $permissions;

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

    /**
     * Método de autenticación utilizado
     * 
     * @var AuthMethod
     */
    protected AuthMethod $authMethod;

    protected $iss = null;
    /**
     * 
     *
     * @var array
     */
    protected $issuersConfig = [];

    public function __construct(
        string $iss,
        AuthMethod|string $authMethod = AuthMethod::Session,
        ?string $jwtSecureKey = null,
        array $issuersConfig = []
    ) {
        $this->jwtAlgoritm = "HS256";
        $this->sessionKey = "gpdauth_session_id";
        $this->jwtSecureKey = $jwtSecureKey;
        $this->jwtExpirationTimeInSeconds = 1200; // 20 minutos
        
        // Convertir string a enum si es necesario (compatibilidad hacia atrás)
        $this->authMethod = $authMethod instanceof AuthMethod 
            ? $authMethod 
            : AuthMethod::tryFromString($authMethod, AuthMethod::Session);
            
        $this->iss = $iss;
        $this->issuersConfig = $issuersConfig;
    }
    /**
     *
     * 
     * Hay que inicializar sesion, roles y permisos
     * 
     * @param string $username
     * @param string $password
     * @throws Exception
     */
    public abstract function login(string $username, string $password): void;

    /**
     * Sobreescribir este método para hacer un login personalizado
     * Si se usan permisos hay que inicializar valor de la propiedad permissions con un array de AuthSessionPermission
     *
     * @return void
     */
    protected abstract function loginJWT(): void;

    /**
     * Sobreescribir este método para hacer un login personalizado
     * Si se usan permisos hay que inicializar valor de la propiedad permissions con un array de AuthSessionPermission
     *
     * @return void
     */
    protected abstract function loginSession();

    /**
     * Sobreescribir este método para  agregar claims personalizados o modificar los existentes
     *
     * @return array
     */
    protected abstract function getCustomOrModifiedClaims(): array;



    // Crea un JWT actualizado
    public function createNewJWTFromSession(): string
    {
        $session = $this->session;
        $currentDate = new DateTime();
        $session["iat"] = $currentDate->getTimestamp();
        $token = AuthJWTManager::createToken($session, $this->jwtSecureKey, $this->jwtAlgoritm);
        return $token;
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
        return isset($session["sub"]) && !empty($session["sub"]);
    }
    public function getSession(): ?array
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
    /**
     * Localiza un determinado permiso con acceso autorizado
     * Los permisos con acceso denegado retornan null
     *
     * @param string $resource
     * @param string $permissionValue
     * @return AuthSessionPermission|null
     */
    public function findPermission(string $resource, string $permissionValue): ?AuthSessionPermission
    {
        $result = null;
        $permissions = $this->getPermissions();
        /** @var AuthSessionPermission */
        foreach ($permissions as $permission) {
            if ($resource != $permission->getResource() || ($permissionValue != $permission->getValue() && $permission->getValue() != Permission::ALL)) continue;
            if ($permission->getAccess() == Permission::ALLOW) {
                return $permission;
            } else {
                return null;
            }
        }
        return $result;
    }
    /**
     * 
     * Determina si el usuario tiene permiso para un determinado recurso
     * Solo se consideran permisos con acceso autorizado
     * Sobreescribir este método para un servicio personalizado
     * 
     * @param string $resource
     * @param string $permissionValue
     * @param string|null $scope
     * @return boolean
     */
    public function hasPermission(string $resource, string $permissionValue, ?string $scope = null): bool
    {
        $permission = $this->findPermission($resource, $permissionValue, $scope);
        if (!($permission instanceof AuthSessionPermission)) {
            return false;
        }
        if (!empty($scope) && $scope != $permission->getScope()) {
            return false;
        }
        return $permission->getAccess() === Permission::ALLOW;
    }
    /**
     * Determina si el usuario tiene algun permiso para alguno de los recursos
     * Solo se consideran permisos con acceso autorizado
     * Sobreescribir  método hasPermission para un servicio personalizado
     *
     * @param array $resources
     * @param array $permissionsValues
     * @param array|null $scopes
     * @return boolean
     */
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
                    continue;
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
    /**
     * Determina si el usuario tiene todos los permisos para todos los recursos
     * Solo se consideran permisos con acceso autorizado
     * Sobreescribir  método hasPermission para un servicio personalizado
     * @param array $resources
     * @param array $permissionsValues
     * @param array|null $scopes
     * @return boolean
     */
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
                    continue;
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
        return $this->roles;
    }
    /**
     * Recupera la lista de permisos asignada
     * @return array AuthSessionPerission[]
     */
    public function getPermissions(): array
    {
        return $this->permissions ?? [];
    }


    public function getAuthId(): ?string
    {
        $session = $this->getSession();
        return $session["sub"] ?? null;
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
    public function setJwtAlgoritm(string $jwtAlgoritm): self
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
    public function setSessionKey(string $sessionKey): self
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
    public function setJwtSecureKey(?string $jwtSecureKey): self
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
    public function setjwtExpirationTimeInSeconds(int $jwtExpirationTimeInSeconds): self
    {
        $this->jwtExpirationTimeInSeconds = $jwtExpirationTimeInSeconds;

        return $this;
    }


    /**
     * Inicializa los datos de la sesión objtenidos ya se de sesion php o de jwt
     * Para inicio seción personalizado Sobreescribir los métodos de login correspondientes
     *
     * @return void
     */
    public function initSession()
    {
        if ($this->authMethod === AuthMethod::Jwt) {
            $this->loginJWT();
        }
        if ($this->authMethod === AuthMethod::Session) {
            $this->loginSession();
        }
        if ($this->authMethod === AuthMethod::SessionOrJwt) {
            $this->loginSession();
            if (empty($this->session)) {
                $this->loginJWT();
            }
        }
        if ($this->authMethod === AuthMethod::JwtOrSession) {
            $this->loginJWT();
            if (empty($this->session)) {
                $this->loginSession();
            }
        }
    }

    protected function getSessionFromJWT()
    {
        $jwt = AuthJWTManager::retriveJWT();
        if (empty($jwt)) {
            return;
        }
        if (empty($this->jwtAlgoritm) || empty($this->jwtSecureKey)) {
            throw new Exception("Invalid JWT configuration. SecureKey or Algoritm are missing");
        }
        try {
            $jwtSecureKey = $this->jwtSecureKey;
            $jwtAlgoritm = $this->jwtAlgoritm;
            $requestIss = AuthJWTManager::getISSNoVerified($jwt);
            if (!empty($requestIss) && $requestIss != $this->iss) {
                $requestIssConfig = $this->getIssuerConfig($requestIss);
                $jwtSecureKey = $requestIssConfig[AuthConfigKey::JwtSecureKey->value] ?? $this->jwtSecureKey;
                $jwtAlgoritm = $requestIssConfig[AuthConfigKey::JwtAlgorithm->value] ?? $this->jwtAlgoritm;
            }
            $jwtData = AuthJWTManager::getJWTData($jwt, $jwtSecureKey, $jwtAlgoritm);
            return $jwtData;
        } catch (Exception $e) {
            $this->clearSession();
            return null;
        }
    }


    protected function getUsernameFromPHPSession(): ?string
    {
        $username = $_SESSION[$this->sessionKey] ?? null;
        if (empty($username)) {
            return null;
        }
        return $username;
    }

    protected function getUsernameFromSessionData(array $sessionData)
    {
        $sub = $sessionData["sub"] ?? null;
        return $sub;
    }



    protected function clearSession(): void
    {
        $this->session = null;
        $this->permissions = null;
        $this->roles = null;
        $this->user = null;
    }


    /**
     * Realiza el login asignando directamente al usuario
     */
    public function setSession(array $session): self
    {
        $this->clearSession();
        $this->session = $session;
        $this->user = $this->sessionToUser($this->session);
        if ($this->authMethod->usesSession()) {
            $_SESSION[$this->sessionKey] = $session["sub"] ?? null;
        }
        return $this;
    }

    protected function getISS()
    {
        return $this->iss;
    }


    protected function sessionToUser(?array $session): ?AuthSessionUser
    {
        if (empty($session)) {
            return null;
        }
        $user = new AuthSessionUser();
        $user->setFullName($session["name"] ?? null)
            ->setFirstName($session["given_name"] ?? null)
            ->setLastName($session["family_name"] ?? null)
            ->setEmail($session["email"] ?? null)
            ->setPicture($session["picture"] ?? null)
            ->setUsername($session["sub"] ?? null);
        return $user;
    }



    private function getIssuerConfig(string $iss)
    {

        $config = $this->issuersConfig[$iss] ?? [];
        return $config;
    }

    /**
     *
     * @param array|null $roles string[]
     * @return self
     */
    protected  function setRoles(array $roles): self
    {
        $this->roles = $roles;
        return $this;
    }

    /**
     * 
     *
     * @param array|null $permissions AuthSessionPerission[]
     * @return self
     */
    protected function setPermissions(array $permissions): self
    {
        $this->permissions = $permissions;
        return $this;
    }


    /**
     * Recupera los roles permitidos para un issuer
     *
     * @param string $iss
     * @param array|null $sessionRoles
     * @return array
     */
    protected function filterIssRoles(string $iss, ?array $sessionRoles): array
    {
        if (empty($sessionRoles)) {
            return [];
        }
        $config = $this->getIssuerConfig($iss);
        $issRoles = $config[AuthConfigKey::AuthIssAllowedRoles->value] ?? [];
        $allowedRoles = [];
        foreach ($sessionRoles as $role) {
            $allowedRole = $issRoles[$role] ?? null;
            if (!empty($allowedRole)) {
                $allowedRoles[] = $allowedRole;
            }
        }
        return $allowedRoles;
    }
}
