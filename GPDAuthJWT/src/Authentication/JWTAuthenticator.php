<?php

namespace GPDAuthJWT\Authentication;

use GPDAuth\Contracts\AuthenticatedUserInterface;
use GPDAuthJWT\Contracts\ApiConsumerRepositoryInterface;
use GPDAuthJWT\Contracts\JWTAuthenticatorInterface;
use GPDAuthJWT\Contracts\JWTTrustIssuerRepositoryInterface;
use GPDAuthJWT\Contracts\JWTUserRepositoryInterfaces;
use GPDAuthJWT\DTO\AuthenticationResult;
use GPDAuthJWT\Library\JwtUtilities;

class JWTAuthenticator implements JWTAuthenticatorInterface
{

    public function __construct(
        protected JWTTrustIssuerRepositoryInterface $issuerRepository,
        protected ApiConsumerRepositoryInterface $apiConsumerRepository,
        protected JWTUserRepositoryInterfaces $userRepository,
        protected int $maxTokenLifetime = 3600
    ) {}

    protected function validateAndDecode(string $jwt, array $header, array $payload): array
    {

        $iss = $payload['iss'] ?? null;
        $kid = $header['kid'] ?? null;
        if (!$iss) {
            throw new \RuntimeException('Missing issuer');
        }
        if (!$kid) {
            throw new \RuntimeException('Missing key ID');
        }
        $isValidIssuer = $this->issuerRepository->isTrustedIssuer($iss);
        if (!$isValidIssuer) {
            throw new \RuntimeException('Untrusted issuer');
        }
        $jwk = $this->issuerRepository->fetchJsonWebKeyByKeyId($iss, $kid);
        if (empty($jwk)) {
            throw new \RuntimeException('Invalid kid');
        }

        if (!$jwk) {
            throw new \RuntimeException('Invalid key ID');
        }

        $publicKey = JwtUtilities::parsePublicKeyFromJWK($jwk);

        if (!$publicKey) {
            throw new \RuntimeException('Could not extract public key');
        }
        $algorithm = $header['alg'] ?? 'RS256';
        $validAlgorithm = $this->issuerRepository->getIssuerAlgorithm($iss);
        if ($algorithm !== $validAlgorithm) {
            throw new \RuntimeException('Invalid algorithm');
        }
        // Decodificar y verificar el JWT
        $decoded =  JwtUtilities::decodeAndVerify($jwt, $publicKey, asArray: true);

        // Validar audience
        $audience = is_array($decoded['aud']) ? $decoded['aud'][0] : $decoded['aud'];

        if (!$this->issuerRepository->isValidAudience($iss, $audience)) {
            throw new \RuntimeException('Invalid audience');
        }

        //Expiración (firebase lo valida, pero mejor explícito)
        if ($decoded['exp'] < time() || $decoded["exp"] > time() + $this->maxTokenLifetime || $decoded["exp"] > $decoded["iat"] + $this->maxTokenLifetime) {
            throw new \RuntimeException('Token expired');
        }
        // Validar que el token no tenga una fecha de expiración demasiado lejana para evitar tokens eternos
        if ($decoded["exp"] > time() + $this->maxTokenLifetime || $decoded["exp"] > $decoded["iat"] + $this->maxTokenLifetime) {
            throw new \RuntimeException('Token expiration too far in the future');
        }


        return $decoded;
    }

    protected function isClientCredentialsToken(array $payload): bool
    {
        $isM2M =
            ($payload['gty'] ?? null) === 'client-credentials'
            || isset($payload['client_id'])
            || (isset($payload['azp']) && $payload['sub'] === $payload['azp']);
        return $isM2M;
    }

    protected function resolveHumanUser(array $decoded): AuthenticatedUserInterface
    {
        $issuer = $decoded['iss'] ?? null;
        $roles = JwtUtilities::extractRoles($decoded);
        $allowedRoles = $this->issuerRepository->getAllowedRolesForIssuer($issuer, $roles);
        $authenticatedUser = $this->userRepository->getUserFromPayload($decoded, $allowedRoles);
        return $authenticatedUser;
    }

    protected function resolveM2MConsumer(array $decoded): AuthenticatedUserInterface
    {
        $roles = JwtUtilities::extractRoles($decoded);
        $consumerId = $this->apiConsumerRepository->getConsumerIdFromJwtPayload($decoded);
        $consumerName = $this->apiConsumerRepository->getConsumerName($consumerId);
        if (!$consumerId || !$consumerName) {
            throw new \RuntimeException('Invalid client credentials');
        }
        $isTruestedConsumer = $this->apiConsumerRepository->isTrustedConsumer($consumerId);
        if (!$isTruestedConsumer) {
            throw new \RuntimeException('Untrusted consumer');
        }
        $permissions = $this->apiConsumerRepository->getValidPermissionsForConsumer($consumerId, $decoded);
        $allowedRoles = $this->apiConsumerRepository->getAllowedRolesForIssuer($consumerId, $roles);
        $authenticatedUser = $this->userRepository->getM2MUserFromPayload($decoded, $permissions, $allowedRoles);
        return $authenticatedUser;
    }

    /**
     * Valida el JWT y recupera un usuario autenticado.
     * Lanza excepciones si el token no es válido, ha expirado, el emisor no es confiable,
     * o si es un token M2M sin consumidor válido o confiable.
     *
     * @param string $jwt
     * @return AuthenticationResult
     */
    public function authenticate(string $jwt): AuthenticationResult
    {
        $unverified = JwtUtilities::decodeUnverified($jwt);
        $header = (array) $unverified->getHeader();
        $payload = (array) $unverified->getPayload();
        $decoded = $this->validateAndDecode($jwt, $header, $payload);
        $isM2M = $this->isClientCredentialsToken($decoded);
        $authenticatedUser = $isM2M
            ? $this->resolveM2MConsumer($decoded)
            : $this->resolveHumanUser($decoded);

        return new AuthenticationResult($authenticatedUser, $decoded, $header);
    }
}
