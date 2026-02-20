<?php

namespace GPDAuthJWT\Contracts;

use GPDAuthJWT\Models\AuthToken;

/**
 * Interface para el repositorio de tokens de autenticación
 */
interface TokenRepositoryInterface
{
    /**
     * Guarda un refresh token
     *
     * @param string $token
     * @param string $userId
     * @param \DateTime $expiresAt
     * @param array $metadata Información adicional del token
     * @return bool
     */
    public function saveRefreshToken(string $token, string $userId, \DateTime $expiresAt, array $metadata = []): bool;

    /**
     * Valida si un refresh token es válido
     *
     * @param string $token
     * @return bool
     */
    public function isRefreshTokenValid(string $token): bool;

    /**
     * Obtiene la información de un refresh token
     *
     * @param string $token
     * @return AuthToken|null
     */
    public function getRefreshToken(string $token): ?AuthToken;

    /**
     * Revoca un refresh token específico
     *
     * @param string $token
     * @return bool
     */
    public function revokeRefreshToken(string $token): bool;

    /**
     * Revoca todos los refresh tokens de un usuario
     *
     * @param string $userId
     * @return bool
     */
    public function revokeAllUserRefreshTokens(string $userId): bool;

    /**
     * Limpia tokens expirados
     *
     * @return int Número de tokens eliminados
     */
    public function cleanExpiredTokens(): int;

    /**
     * Guarda un token en lista negra
     *
     * @param string $token
     * @param \DateTime $expiresAt
     * @return bool
     */
    public function blacklistToken(string $token, \DateTime $expiresAt): bool;

    /**
     * Verifica si un token está en lista negra
     *
     * @param string $token
     * @return bool
     */
    public function isTokenBlacklisted(string $token): bool;
}
