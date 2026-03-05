<?php
// src/Middleware/JwtAuthMiddleware.php

namespace GPDAuthJWT\Middleware;


use GPDAuth\Models\AuthenticatedUser;
use GPDAuth\Contracts\AuthenticatedUserInterface;
use GPDAuth\Models\AuthenticatedUserType;
use GPDAuthJWT\Entities\ApiConsumer;
use GPDAuthJWT\Entities\TrustedIssuer;
use GPDAuthJWT\Library\JwtUtilities;
use GPDAuthJWT\Contracts\ApiConsumerRepositoryInterface;
use GPDAuthJWT\Contracts\JWTTrustIssuerRepositoryInterface;
use Laminas\Diactoros\Response\JsonResponse;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Psr\Http\Server\MiddlewareInterface;

class JwtAuthMiddleware implements MiddlewareInterface
{
    /**
     * Valida la autenticación JWT en la solicitud
     * Agrega el usuario autenticado a los atributos de la solicitud con el atributo identity
     * Cuando exitUnAuthorized es true, responde con 401 si la autenticación falla (Aplica para rutas protegidas)
     * Cuando exitUnAuthorized es false, continúa la cadena de middleware si la autenticación falla (Aplica para rutas públicas o para GraphQL para validar cada query, la validación se hace en los resolvers o middleware de los resolvers, con los datos del atributo identity de request)
     *
     * @param JWTTrustIssuerRepositoryInterface $issuerRepository
     * @param ApiConsumerRepositoryInterface $apiConsumerRepository
     * @param boolean $exitUnAuthorized
     */
    public function __construct(
        private JWTTrustIssuerRepositoryInterface $issuerRepository,
        private ApiConsumerRepositoryInterface $apiConsumerRepository,
        private string $identityKey = AuthenticatedUserInterface::class,
        private bool $exitUnAuthorized = true,
        private int $maxTokenLifetime = 3600
    ) {}

    public function process(
        ServerRequestInterface $request,
        RequestHandlerInterface $handler
    ): ResponseInterface {

        $jwt = JwtUtilities::extractJWTFromRequest($request);
        if ($jwt === null) {
            if ($this->exitUnAuthorized) {
                return $this->unauthorized('Missing token');
            } else {
                return $handler->handle($request);
            }
        }
        try {

            // Decodificar sin verificar para obtener header y payload
            $unverified = JwtUtilities::decodeUnverified($jwt);
            $header = (array) $unverified->getHeader();
            $payload = (array) $unverified->getPayload();

            // Buscar el issuer en la base de datos
            $trustedIssuer = $this->getValidIssuer($payload);

            $decoded = $this->decodeAndValidate($jwt, $header, $payload, $trustedIssuer);

            // ¿Es M2M?
            $isM2M =
                ($decoded['gty'] ?? null) === 'client-credentials'
                || isset($decoded['client_id'])
                || (isset($decoded['azp']) && $decoded['sub'] === $decoded['azp']);
            if ($isM2M) {
                // M2M solo tiene permisos de recurso basados en scopes, no roles ni datos de usuario
                $consumer = $this->extractConsumerFromJwt($decoded);
                $this->enforceM2MWhitelist($consumer);
                $permissions = $this->apiConsumerRepository->getAllowedPermissions($consumer, $decoded);
                $authenticatedUser = (new AuthenticatedUser())
                    ->setFullName($consumer->getName())
                    ->setType(AuthenticatedUserType::API_CLIENT)
                    ->setId($consumer->getId())
                    ->setUsername($decoded['iss'] . '|' . $decoded['azp'])
                    ->setFullName($decoded['iss'] . '|' . $decoded['azp'])
                    ->setRoles([])
                    ->setPermissions($permissions);
            } else {
                $username = $decoded['iss'] . '|' . $decoded['sub'];
                $roles = JwtUtilities::extractRoles($decoded);
                $allowedRoles = $this->issuerRepository->filterAllowedRolesForIssuer($trustedIssuer, $roles);
                // Para usuarios humanos, se pueden mapear roles y permisos adicionales desde la base de datos si es necesario, usando el sub o el azp como identificador
                $authenticatedUser = (new AuthenticatedUser())
                    ->setType(AuthenticatedUserType::EXTERN_USER)
                    ->setId($username)
                    ->setUsername($username)
                    ->setFullName($decoded["name"] ?? $username)
                    ->setEmail($decoded['email'] ?? null)
                    ->setFirstName($decoded['given_name'] ?? null)
                    ->setLastName($decoded['family_name'] ?? null)
                    ->setPicture($decoded['picture'] ?? null)
                    ->setRoles($allowedRoles)
                    ->setPermissions([]);
            }
            $request = $request->withAttribute($this->identityKey, $authenticatedUser);
            $request = $request->withAttribute('jwt_payload', $decoded);
            return $handler->handle(
                $request
            );
        } catch (\Throwable $e) {

            if ($this->exitUnAuthorized) {
                return $this->unauthorized($e->getMessage());
            } else {
                return $handler->handle($request);
            }
        }
    }

    private function getValidIssuer(array $payload): TrustedIssuer
    {
        $iss = $payload['iss'] ?? null;
        if (!$iss) {
            throw new \RuntimeException('Missing issuer');
        }
        $trustedIssuer = $this->issuerRepository->findIssuer($iss);
        if (!($trustedIssuer instanceof TrustedIssuer) || !$trustedIssuer->isActive()) {
            throw new \RuntimeException('Untrusted issuer');
        }
        return $trustedIssuer;
    }

    private function decodeAndValidate(string $jwt, array $header, array $payload, TrustedIssuer $trustedIssuer): array
    {

        $iss = $payload['iss'] ?? null;
        $kid = $header['kid'] ?? null;

        if (!$kid) {
            throw new \RuntimeException('Missing key ID');
        }

        $jwk = $this->issuerRepository->fetchJWKByKid($trustedIssuer, $kid);
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
        $algorithm = $header->alg ?? 'RS256';
        $validAlgorithm = $trustedIssuer->getAlg();
        if ($algorithm !== $validAlgorithm) {
            throw new \RuntimeException('Invalid algorithm');
        }
        // Decodificar y verificar el JWT
        $decoded = (array) JwtUtilities::decodeAndVerify($jwt, $publicKey);

        // Validar audience
        $audience = is_array($decoded['aud']) ? $decoded['aud'][0] : $decoded['aud'];

        if (!$this->issuerRepository->isValidAudience($trustedIssuer, $audience)) {
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


    private function extractConsumerFromJwt(array $jwt): ?ApiConsumer
    {

        $clientId = $jwt['azp'] ?? $jwt['client_id'] ?? null;

        if ($clientId) {
            $apiConsumer = $this->apiConsumerRepository->findByIdentifier($clientId);
            if ($apiConsumer && $apiConsumer->isActive()) {
                return $apiConsumer;
            }
        }

        return null;
    }
    private function enforceM2MWhitelist(?ApiConsumer $apiConsumer): void
    {

        if (!$apiConsumer || !$apiConsumer->isActive()) {
            throw new \RuntimeException('Client not allowed');
        }
    }

    private function unauthorized(string $message): ResponseInterface
    {
        return new JsonResponse([
            'error' => 'unauthorized',
            'message' => $message,
        ], 401);
    }
}
