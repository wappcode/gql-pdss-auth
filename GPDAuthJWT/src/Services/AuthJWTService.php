<?php

namespace GPDAuth\Services;

use DateTime;
use Exception;
use Doctrine\ORM\EntityManager;
use GPDAuth\Entities\PermissionAccess;
use GPDAuth\Entities\User;
use GPDAuth\Library\AuthServiceInterface;
use GPDAuth\Library\TokenService;
use GPDAuth\Library\AuthenticationType;
use GPDAuth\Library\AuthJWTManager;
use GPDAuth\Library\InvalidUserException;
use GPDAuth\Models\AuthenticatedUser;
use GPDAuth\Models\ResourcePermission;
use GPDAuth\Models\UserRepositoryInterface;
use GPDAuth\Models\TokenRepositoryInterface;
use GPDAuthJWT\Entities\ApiConsumer;
use GPDAuthJWT\Entities\TrustedIssuer;
use GPDAuthJWT\Services\JWTTrustIssuerRepository;
use GPDCore\Library\IContextService;

@session_start();

/**
 * Servicio de autenticación híbrido que soporta:
 * - Sesiones PHP para navegadores web
 * - Tokens JWT (Access + Refresh) para APIs
 */
class AuthService extends AbstractAuthService
{




    private JWTTrustIssuerRepository $issRepository;
    private IContextService $context;
    private string $sessionKey;


    public function __construct(
        IContextService $context,
        JWTTrustIssuerRepository $issRepository,
        string $sessionKey = 'gpdauth_session_id'
    ) {
        $this->context = $context;
        $this->issRepository = $issRepository;
        $this->sessionKey = $sessionKey;
        $this->init();
    }

    public  function login(string $clientId, string $secret, string $grantType): void
    {
        $this->validateClient($clientId, $secret, $grantType);
    }
    public function logout(): void
    {
        $_SESSION[$this->sessionKey]    = null;
        $this->authenticatedUser = null;
    }

    public function setSession($userId, $grant): void
    {
        $_SESSION[$this->sessionKey]["identifier"] = $userId ?? null;
        $_SESSION[$this->sessionKey]["grant"] = $grant ?? null;
    }

    private function setAuthenticatedUser(): void
    {
        $userId = $_SESSION[$this->sessionKey]["identifier"] ?? null;
        if ($userId !== null) {
            $this->authenticatedUser = $this->userRepository->findById($userId);
        }
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

    private function validateClient($clientId, $secret, $grantType)
    {
        $entityManager = $this->context->getEntityManager();
        $client = $this->$entityManager->createQueryBuilder()->from(ApiConsumer::class, 'c')
            ->select(['c', 'g'])
            ->innerJoin('c.grants', 'g')
            ->where('c.identifier = :identifier')
            ->setParameter('identifier', $clientId)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();


        if (!($client instanceof ApiConsumer) || $client->getStatus() !== 'active') {
            http_response_code(401);
            echo json_encode(['error' => 'invalid_client']);
            exit;
        }
        $secretHash = $client->getSecretHash();
        if (!password_verify($secret, $secretHash)) {
            http_response_code(401);
            echo json_encode(['error' => 'invalid_client']);
            exit;
        }
        $allowedGrants = array_map(function ($grant) {
            return $grant->getGrantType();
        }, $client->getGrants()->toArray());
        if (!in_array($grantType, $allowedGrants)) {
            http_response_code(401);
            echo json_encode(['error' => 'unauthorized_client']);
            exit;
        }
    }
}
