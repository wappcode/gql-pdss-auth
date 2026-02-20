<?php

namespace GPDAuthJWT\Services;

use DateTime;
use Exception;
use GPDAuth\Library\AuthJWTManager;
use GPDAuthJWT\Contracts\TokenRepositoryInterface;
use GPDAuthJWT\Models\AuthToken;

/**
 * Servicio para la gestión de tokens de autenticación (access y refresh)
 */
class TokenService
{
    private TokenRepositoryInterface $tokenRepository;
    private string $jwtSecret;
    private int $accessTokenExpiration;
    private int $refreshTokenExpiration;

    public function __construct(
        TokenRepositoryInterface $tokenRepository,
        string $jwtSecret,
        int $accessTokenExpiration = 900, // 15 minutos por defecto
        int $refreshTokenExpiration = 604800 // 7 días por defecto
    ) {
        $this->tokenRepository = $tokenRepository;
        $this->jwtSecret = $jwtSecret;
        $this->accessTokenExpiration = $accessTokenExpiration;
        $this->refreshTokenExpiration = $refreshTokenExpiration;
    }

    /**
     * Genera un par de tokens (access y refresh) para un usuario
     */
    public function generateTokenPair(
        string $userId,
        array $claims = [],
        array $metadata = []
    ): array {
        $accessToken = $this->generateAccessToken($userId, $claims);
        $refreshToken = $this->generateRefreshToken($userId, $metadata);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken['token'],
            'expires_in' => $this->accessTokenExpiration,
            'token_type' => 'Bearer'
        ];
    }

    /**
     * Genera un access token JWT
     */
    public function generateAccessToken(string $userId, array $claims = []): string
    {
        $now = new DateTime();
        $expiration = clone $now;
        $expiration->modify("+{$this->accessTokenExpiration} seconds");

        $payload = array_merge([
            'sub' => $userId,
            'iat' => $now->getTimestamp(),
            'exp' => $expiration->getTimestamp(),
            'type' => 'access'
        ], $claims);

        return AuthJWTManager::createToken($payload, $this->jwtSecret);
    }

    /**
     * Genera un refresh token
     */
    public function generateRefreshToken(string $userId, array $metadata = []): array
    {
        $token = $this->generateSecureToken();
        $expiresAt = new DateTime();
        $expiresAt->modify("+{$this->refreshTokenExpiration} seconds");

        $authToken = new AuthToken(
            $token,
            $userId,
            $expiresAt,
            AuthToken::TYPE_REFRESH,
            $metadata
        );

        $this->tokenRepository->saveRefreshToken(
            $token,
            $userId,
            $expiresAt,
            $metadata
        );

        return [
            'token' => $token,
            'expires_at' => $expiresAt->format('Y-m-d H:i:s')
        ];
    }

    /**
     * Refresca un access token usando un refresh token
     */
    public function refreshAccessToken(string $refreshToken, array $additionalClaims = []): array
    {
        if (!$this->tokenRepository->isRefreshTokenValid($refreshToken)) {
            throw new Exception('Invalid refresh token');
        }

        $tokenData = $this->tokenRepository->getRefreshToken($refreshToken);
        if (!$tokenData || $tokenData->isExpired()) {
            throw new Exception('Refresh token expired or not found');
        }

        $userId = $tokenData->getUserId();
        $metadata = $tokenData->getMetadata();

        // Generar nuevo access token
        $accessToken = $this->generateAccessToken($userId, $additionalClaims);

        return [
            'access_token' => $accessToken,
            'expires_in' => $this->accessTokenExpiration,
            'token_type' => 'Bearer'
        ];
    }

    /**
     * Valida un access token
     */
    public function validateAccessToken(string $token): ?array
    {
        try {
            // Verificar si está en lista negra
            if ($this->tokenRepository->isTokenBlacklisted($token)) {
                return null;
            }

            $payload = (array) AuthJWTManager::decode($token, $this->jwtSecret);

            // Verificar que sea un access token
            if (!isset($payload['type']) || $payload['type'] !== 'access') {
                return null;
            }

            // Verificar expiración
            if (isset($payload['exp']) && $payload['exp'] < time()) {
                return null;
            }

            return $payload;
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Revoca un refresh token
     */
    public function revokeRefreshToken(string $refreshToken): bool
    {
        return $this->tokenRepository->revokeRefreshToken($refreshToken);
    }

    /**
     * Revoca todos los tokens de un usuario
     */
    public function revokeAllUserTokens(string $userId): bool
    {
        return $this->tokenRepository->revokeAllUserRefreshTokens($userId);
    }

    /**
     * Agrega un token a la lista negra
     */
    public function blacklistToken(string $token): bool
    {
        try {
            $payload = (array) AuthJWTManager::decode($token, $this->jwtSecret);
            $expiresAt = new DateTime();

            if (isset($payload['exp'])) {
                $expiresAt->setTimestamp($payload['exp']);
            } else {
                // Si no tiene expiración, usar un tiempo por defecto
                $expiresAt->modify('+1 day');
            }

            return $this->tokenRepository->blacklistToken($token, $expiresAt);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * Genera un token seguro aleatorio
     */
    private function generateSecureToken(): string
    {
        return bin2hex(random_bytes(32));
    }

    /**
     * Limpia tokens expirados
     */
    public function cleanupExpiredTokens(): int
    {
        return $this->tokenRepository->cleanExpiredTokens();
    }

    /**
     * Obtiene información de un token sin validar
     */
    public function getTokenInfo(string $token): ?array
    {
        try {
            return (array) AuthJWTManager::decode($token, $this->jwtSecret);
        } catch (Exception $e) {
            return null;
        }
    }

    /**
     * Configura el tiempo de expiración de access tokens
     */
    public function setAccessTokenExpiration(int $seconds): self
    {
        $this->accessTokenExpiration = $seconds;
        return $this;
    }

    /**
     * Configura el tiempo de expiración de refresh tokens
     */
    public function setRefreshTokenExpiration(int $seconds): self
    {
        $this->refreshTokenExpiration = $seconds;
        return $this;
    }
}
