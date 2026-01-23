<?php

namespace GPDAuth\Services;

use DateTime;
use Exception;
use Doctrine\ORM\EntityManager;
use GPDAuth\Entities\User;
use GPDAuth\Library\AuthServiceInterface;
use GPDAuth\Library\TokenService;
use GPDAuth\Library\AuthenticationType;
use GPDAuth\Library\InvalidUserException;
use GPDAuth\Models\ResourcePermission;
use GPDAuth\Models\UserRepositoryInterface;
use GPDAuth\Models\TokenRepositoryInterface;

@session_start();

/**
 * Servicio de autenticación híbrido que soporta:
 * - Sesiones PHP para navegadores web
 * - Tokens JWT (Access + Refresh) para APIs
 */
class AuthService extends AuthSessionService
{
    private EntityManager $entityManager;
    private UserRepositoryInterface $userRepository;
    private TokenRepositoryInterface $tokenRepository;
    private TokenService $tokenService;
    private string $iss;
    private string $sessionKey;
    private string $jwtSecret;
    
    // Estado de la sesión actual
    private ?array $session = null;
    private ?User $currentUser = null;
    private array $roles = [];
    private array $permissions = [];

    public function __construct(
        string $iss,
        UserRepositoryInterface $userRepository,
        TokenRepositoryInterface $tokenRepository,
        EntityManager $entityManager,
        ?string $jwtSecret = null,
        string $sessionKey = 'gpdauth_session_id'
    ) {
        $this->iss = $iss;
        $this->userRepository = $userRepository;
        $this->tokenRepository = $tokenRepository;
        $this->entityManager = $entityManager;
        $this->jwtSecret = $jwtSecret ?? 'default_secret_change_in_production';
        $this->sessionKey = $sessionKey;
        
        // Inicializar el servicio de tokens
        $this->tokenService = new TokenService(
            $tokenRepository,
            $this->jwtSecret,
            900,  // Access token: 15 minutos
            604800 // Refresh token: 7 días
        );
    }



    /**
     * Login para APIs que retorna tokens JWT
     *
     * @param string $username
     * @param string $password
     * @param array $metadata Metadatos adicionales para el refresh token
     * @return array Tokens y información de expiración
     * @throws InvalidUserException
     */
    public function apiLogin(string $username, string $password, array $metadata = []): array
    {
        $user = $this->userRepository->validateCredentials($username, $password);
        if (!$user) {
            throw new InvalidUserException("Credenciales inválidas");
        }

        // Actualizar último acceso
        $this->userRepository->updateLastAccess($user);

        // Crear claims para el token
        $claims = [
            'sub' => $user->getUsername(),
            'name' => trim(($user->getFirstName() ?? '') . ' ' . ($user->getLastName() ?? '')),
            'email' => $user->getEmail() ?? '',
            'iss' => $this->iss
        ];

        // Generar par de tokens
        return $this->tokenService->generateTokenPair(
            $user->getUsername(),
            $claims,
            array_merge($metadata, [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? 'unknown'
            ])
        );
    }

    /**
     * Autenticación automática que detecta el tipo de request
     * y procesa sesión o token según corresponda
     *
     * @return bool True si la autenticación fue exitosa
     */
    public function authenticate(): bool
    {
        $authenticationType = AuthenticationType::detect();

        switch ($authenticationType) {
            case AuthenticationType::ACCESS_TOKEN:
                return $this->authenticateWithToken();

            case AuthenticationType::SESSION:
                return $this->authenticateWithSession();
        }

        return false;
    }

    /**
     * Autentica usando token Bearer
     *
     * @return bool
     */
    private function authenticateWithToken(): bool
    {
        $token = AuthenticationType::extractBearerToken();
        if (!$token) {
            return false;
        }

        $tokenData = $this->tokenService->validateAccessToken($token);
        if (!$tokenData) {
            return false;
        }

        // Establecer datos de sesión desde el token
        $this->session = $tokenData;
        
        // Cargar usuario y sus datos
        $username = $tokenData['sub'] ?? null;
        if ($username) {
            $this->currentUser = $this->userRepository->findByUsername($username);
            if ($this->currentUser) {
                $this->loadUserRolesAndPermissions();
                return true;
            }
        }

        return false;
    }

    /**
     * Autentica usando sesión PHP
     *
     * @return bool
     */
    private function authenticateWithSession(): bool
    {
        $username = $_SESSION[$this->sessionKey] ?? null;
        if (!$username) {
            return false;
        }

        $user = $this->userRepository->findByUsername($username);
        if (!$user) {
            // Limpiar sesión inválida
            unset($_SESSION[$this->sessionKey]);
            return false;
        }

        $this->currentUser = $user;
        $this->session = [
            'sub' => $user->getUsername(),
            'name' => trim(($user->getFirstName() ?? '') . ' ' . ($user->getLastName() ?? '')),
            'email' => $user->getEmail() ?? '',
            'iss' => $this->iss
        ];

        $this->loadUserRolesAndPermissions();
        return true;
    }

    /**
     * Refresca un access token usando un refresh token
     *
     * @param string $refreshToken
     * @return array Nuevo access token
     * @throws Exception
     */
    public function refreshToken(string $refreshToken): array
    {
        // Primero intentar validar el refresh token para obtener el userId
        try {
            $tempToken = \Firebase\JWT\JWT::decode($refreshToken, new \Firebase\JWT\Key($this->jwtSecret, 'HS256'));
            $userId = $tempToken->sub;
        } catch (\Exception $e) {
            throw new Exception('Refresh token inválido');
        }
        $user = $this->userRepository->findByUsername($userId);
        if (!$user) {
            throw new Exception('Usuario no encontrado');
        }

        $claims = [
            'sub' => $user->getUsername(),
            'name' => trim(($user->getFirstName() ?? '') . ' ' . ($user->getLastName() ?? '')),
            'email' => $user->getEmail() ?? '',
            'iss' => $this->iss
        ];

        return $this->tokenService->refreshAccessToken($refreshToken, $claims);
    }

    /**
     * Logout que maneja tanto sesiones como tokens
     *
     * @param string|null $refreshToken Token específico a revocar
     */
    public function logout(?string $refreshToken = null): void
    {
        // Revocar tokens si se proporciona refresh token
        if ($refreshToken) {
            $this->tokenService->revokeRefreshToken($refreshToken);
        }

        // Agregar access token actual a lista negra si existe
        $currentToken = AuthenticationType::extractBearerToken();
        if ($currentToken) {
            $this->tokenService->blacklistToken($currentToken);
        }

        // Limpiar sesión PHP
        unset($_SESSION[$this->sessionKey]);
        
        // Limpiar estado interno
        $this->clearSessionData();
    }

    /**
     * Logout completo que revoca TODOS los tokens del usuario
     */
    public function logoutEverywhere(): void
    {
        if ($this->currentUser) {
            $this->tokenService->revokeAllUserTokens($this->currentUser->getUsername());
        }

        $this->logout();
    }

    /**
     * Verifica si el usuario está autenticado
     *
     * @return bool
     */
    public function isSigned(): bool
    {
        return $this->session !== null && !empty($this->session['sub']);
    }

    /**
     * Obtiene el ID/username del usuario autenticado
     *
     * @return string|null
     */
    public function getAuthId(): ?string
    {
        return $this->session['sub'] ?? null;
    }

    /**
     * Obtiene el usuario actual
     *
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->currentUser;
    }

    /**
     * Obtiene información completa del usuario actual
     *
     * @return array|null
     */
    public function getUserInfo(): ?array
    {
        if (!$this->isSigned() || !$this->currentUser) {
            return null;
        }

        return [
            'username' => $this->currentUser->getUsername(),
            'name' => trim(($this->currentUser->getFirstName() ?? '') . ' ' . ($this->currentUser->getLastName() ?? '')),
            'email' => $this->currentUser->getEmail(),
            'roles' => $this->roles,
            'permissions' => array_map(function($permission) {
                return [
                    'resource' => $permission->getResource(),
                    'value' => $permission->getValue(),
                    'access' => $permission->getAccess(),
                    'scope' => $permission->getScope()
                ];
            }, $this->permissions),
            'session_data' => $this->session
        ];
    }

    /**
     * Verifica si el usuario tiene un rol específico
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return in_array($role, $this->roles);
    }

    /**
     * Verifica si el usuario tiene alguno de los roles especificados
     *
     * @param array $roles
     * @return bool
     */
    public function hasSomeRoles(array $roles): bool
    {
        return !empty(array_intersect($this->roles, $roles));
    }

    /**
     * Verifica si el usuario tiene todos los roles especificados
     *
     * @param array $roles
     * @return bool
     */
    public function hasAllRoles(array $roles): bool
    {
        return empty(array_diff($roles, $this->roles));
    }

    /**
     * Verifica si el usuario tiene un permiso específico
     *
     * @param string $resource
     * @param string $permissionValue
     * @param string|null $scope
     * @return bool
     */
    public function hasPermission(string $resource, string $permissionValue, ?string $scope = null): bool
    {
        foreach ($this->permissions as $permission) {
            if ($permission->getResource() === $resource && 
                ($permission->getValue() === $permissionValue || $permission->getValue() === 'all') &&
                ($scope === null || $permission->getScope() === $scope) &&
                $permission->getAccess() === 'allow') {
                return true;
            }
        }
        return false;
    }

    /**
     * Obtiene todos los roles del usuario
     *
     * @return array
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * Obtiene todos los permisos del usuario
     *
     * @return array
     */
    public function getPermissions(): array
    {
        return $this->permissions;
    }

    /**
     * Carga roles y permisos del usuario actual
     */
    private function loadUserRolesAndPermissions(): void
    {
        if (!$this->currentUser) {
            return;
        }

        // Cargar roles
        $this->roles = [];
        foreach ($this->currentUser->getRoles() as $role) {
            $this->roles[] = $role->getName();
        }

        // Cargar permisos
        $this->permissions = [];
        foreach ($this->currentUser->getRoles() as $role) {
            foreach ($role->getPermissions() as $permission) {
                \$authPermission = new ResourcePermission(
                    $permission->getResource(),
                    $permission->getAccess(),
                    $permission->getValue(),
                    $permission->getScope()
                );
                $this->permissions[] = $authPermission;
            }
        }
    }

    /**
     * Limpia todos los datos de sesión
     */
    private function clearSessionData(): void
    {
        $this->session = null;
        $this->currentUser = null;
        $this->roles = [];
        $this->permissions = [];
    }

    /**
     * Obtiene el arreglo de sesión actual
     *
     * @return array|null
     */
    public function getSession(): ?array
    {
        return $this->session;
    }

    /**
     * Verifica si el usuario tiene alguno de los permisos especificados
     *
     * @param array $resources
     * @param array $permissionsValues
     * @param array|null $scopes
     * @return bool
     */
    public function hasSomePermissions(array $resources, array $permissionsValues, ?array $scopes = null): bool
    {
        foreach ($resources as $resource) {
            foreach ($permissionsValues as $permissionValue) {
                if (empty($scopes)) {
                    if ($this->hasPermission($resource, $permissionValue)) {
                        return true;
                    }
                } else {
                    foreach ($scopes as $scope) {
                        if ($this->hasPermission($resource, $permissionValue, $scope)) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }

    /**
     * Verifica si el usuario tiene todos los permisos especificados
     *
     * @param array $resources
     * @param array $permissionsValues
     * @param array|null $scopes
     * @return bool
     */
    public function hasAllPermissions(array $resources, array $permissionsValues, ?array $scopes = null): bool
    {
        foreach ($resources as $resource) {
            foreach ($permissionsValues as $permissionValue) {
                if (empty($scopes)) {
                    if (!$this->hasPermission($resource, $permissionValue)) {
                        return false;
                    }
                } else {
                    foreach ($scopes as $scope) {
                        if (!$this->hasPermission($resource, $permissionValue, $scope)) {
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }
}
