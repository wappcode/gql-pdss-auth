<?php


namespace GPDAuthJWT\Services;

use Exception;
use GPDAuth\Entities\PermissionAccess;
use GPDAuth\Library\AuthJWTManager;
use GPDAuth\Models\AuthenticatedUser;
use GPDAuth\Models\ResourcePermission;
use GPDAuth\Services\AbstractAuthService;
use GPDAuthJWT\Entities\TrustedIssuer;

class AuthM2MService extends AbstractAuthService
{




    private JWTTrustIssuerRepository $issRepository;

    public function __construct(JWTTrustIssuerRepository $issRepository)
    {

        $this->issRepository = $issRepository;
        $this->init();
    }

    public  function login(string $username, string $password): void
    {
        throw new Exception("Login not supported in M2M Auth Service");
    }

    public  function logout(): void
    {
        throw new Exception("Logout not supported in M2M Auth Service");
    }


    /**
     * Inicializa los datos del usuario autenticado desde el JWT para M2M
     *
     * @return void
     */
    private function init()
    {
        try {
            $validJwt = $this->validateJwt();
            $this->authenticatedUser = $this->createAuthenticatedUserFromJWT($validJwt);
        } catch (Exception $e) {
            // No genera error simplemente se considera que el usuario no está autenticado y no tiene permisos
            $this->authenticatedUser = null;
            error_log("AuthM2MService init error: " . $e->getMessage());
        }
    }



    private function validateJwt(): object
    {
        $jwt = AuthJWTManager::retriveJWT();

        if (empty($jwt)) {
            throw new Exception('Missing token');
        }
        // 1. Decodificar header para leer kid
        // $header = AuthJWTManager::getJWTHeader($jwt);
        $unverifiedJWT = AuthJWTManager::decodeWithoutVerification($jwt);
        $header = $unverifiedJWT->getHeader();
        $payload = $unverifiedJWT->getPayload();

        if (empty($header->kid)) {
            throw new Exception('Missing kid');
        }
        $issuer = $this->issRepository->findIssuer($payload->iss ?? '');
        if (!($issuer instanceof TrustedIssuer) || $issuer->isActive()) {
            throw new Exception('Invalid issuer');
        }
        $jwk = $this->issRepository->fetchJWKByKid($issuer, $header->kid);
        if (empty($jwk)) {
            throw new Exception('Invalid kid');
        }
        $publicKey = AuthJWTManager::getPublicKeyFromJWK($jwk);
        if (empty($publicKey)) {
            throw new Exception('Invalid public key');
        }

        $algorithm = $header->alg ?? 'RS256';
        $validAlgorithm = $issuer->getAlg();

        if ($algorithm !== $validAlgorithm) {
            throw new Exception('Invalid algorithm');
        }
        // 3. Decodificar y verificar firma
        $token = AuthJWTManager::decode($jwt, $publicKey, $algorithm);



        if (!$this->issRepository->isValidAudience($issuer, $token->aud ?? '')) {
            throw new Exception('Invalid audience');
        }

        // 5. Expiración (firebase lo valida, pero mejor explícito)
        if ($token->exp < time()) {
            throw new Exception('Token expired');
        }

        return $token;
    }

    private function createAuthenticatedUserFromJWT(object $jwt): AuthenticatedUser
    {
        $id = $jwt->iss . "_" . $jwt->sub;
        $authenticatedUser = new AuthenticatedUser();
        $authenticatedUser->setId($id);
        $authenticatedUser->setUsername($jwt->sub);
        $authenticatedUser->setFullName($jwt->sub);
        $authenticatedUser->setRoles([]); // M2M no tiene roles
        $permissions = $this->createResourcePermissionsFromJWTScopes([], $jwt);
        $authenticatedUser->setPermissions($permissions);
        return $authenticatedUser;
    }
    /**
     * 
     *
     * @param array $scopes
     * @param object $jwt
     * @return array [ResourcePermission]
     */
    private function createResourcePermissionsFromJWTScopes(array $scopes, object $jwt): array
    {
        $jwtScopes = isset($jwt->scope) ? explode(' ', $jwt->scope) : [];

        $permissions = array_map(function (string $scope) {
            $scopeFormated = str_replace('.', ':', strtolower($scope));
            [$resource, $permissionValue] = explode(':', $scopeFormated, 2);
            $permission = new ResourcePermission($resource, PermissionAccess::ALLOW->value, $permissionValue);
            return $permission;
        }, $jwtScopes);

        return $permissions;
    }
    /**
     * Determina si un JWT es M2M (Client Credentials)
     * Compatible con la mayoría de IdPs (estándar OAuth2/OIDC)
     */
    function isM2MToken(object $payload): bool
    {
        // 1️⃣ Debe tener scopes
        if (empty($payload->scope)) {
            return false;
        }

        // 2️⃣ NO debe tener identidad humana
        $humanClaims = [
            'email',
            'preferred_username',
            'username',
            'name',
            'given_name',
            'family_name',
            'idp'
        ];

        foreach ($humanClaims as $claim) {
            if (isset($payload->$claim)) {
                return false;
            }
        }

        // 3️⃣ sub debe existir
        if (empty($payload->sub)) {
            return false;
        }

        // 4️⃣ azp fuerte señal M2M
        if (isset($payload->azp) && $payload->sub === $payload->azp) {
            return true;
        }

        // 5️⃣ gty es opcional (NO estándar)
        if (($payload->gty ?? null) === 'client-credentials') {
            return true;
        }

        return false;
    }
}
