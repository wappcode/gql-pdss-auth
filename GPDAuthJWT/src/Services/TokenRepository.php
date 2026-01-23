<?php

namespace GPDAuthJWT\Services;

use Doctrine\ORM\EntityManager;
use GPDAuthJWT\Models\AuthToken;
use GPDAuthJWT\Models\TokenRepositoryInterface;

/**
 * Implementación concreta del repositorio de tokens usando Doctrine
 */
class TokenRepository implements TokenRepositoryInterface
{
    private EntityManager $entityManager;

    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function saveRefreshToken(string $token, string $userId, \DateTime $expiresAt, array $metadata = []): bool
    {
        try {
            // Crear o actualizar el token
            $authToken = new AuthToken($userId, $token, $expiresAt, 'refresh');
            $this->entityManager->persist($authToken);
            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isRefreshTokenValid(string $token): bool
    {
        $repository = $this->entityManager->getRepository(AuthToken::class);
        $authToken = $repository->findOneBy(['token' => $token, 'type' => 'refresh']);

        if (!$authToken) {
            return false;
        }

        // Verificar si no ha expirado
        return $authToken->getExpiresAt() > new \DateTime();
    }

    public function getRefreshToken(string $token): ?AuthToken
    {
        $repository = $this->entityManager->getRepository(AuthToken::class);
        return $repository->findOneBy(['token' => $token, 'type' => 'refresh']);
    }

    public function revokeRefreshToken(string $token): bool
    {
        $repository = $this->entityManager->getRepository(AuthToken::class);
        $authToken = $repository->findOneBy(['token' => $token, 'type' => 'refresh']);

        if ($authToken) {
            $this->entityManager->remove($authToken);
            $this->entityManager->flush();
            return true;
        }

        return false;
    }

    public function revokeAllUserRefreshTokens(string $userId): bool
    {
        try {
            $repository = $this->entityManager->getRepository(AuthToken::class);
            $tokens = $repository->findBy(['username' => $userId, 'type' => 'refresh']);

            foreach ($tokens as $token) {
                $this->entityManager->remove($token);
            }

            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function cleanExpiredTokens(): int
    {
        $now = new \DateTime();
        $query = $this->entityManager->createQuery(
            'DELETE FROM GPDAuth\Models\AuthToken t WHERE t.expiresAt < :now'
        );
        $query->setParameter('now', $now);
        return $query->execute();
    }

    public function blacklistToken(string $token, \DateTime $expiresAt): bool
    {
        try {
            // Crear entrada de blacklist para el access token
            $blacklistToken = new AuthToken('blacklist', $token, $expiresAt, 'blacklist');
            $this->entityManager->persist($blacklistToken);
            $this->entityManager->flush();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isTokenBlacklisted(string $token): bool
    {
        $repository = $this->entityManager->getRepository(AuthToken::class);
        $blacklistedToken = $repository->findOneBy(['token' => $token, 'type' => 'blacklist']);

        return $blacklistedToken !== null;
    }

    // Métodos adicionales para compatibilidad con el AuthService actual
    public function revokeAllUserTokens(string $username): void
    {
        $this->revokeAllUserRefreshTokens($username);
    }

    public function findRefreshToken(string $token): ?AuthToken
    {
        return $this->getRefreshToken($token);
    }

    public function blacklistAccessToken(string $token, \DateTime $expiresAt): void
    {
        $this->blacklistToken($token, $expiresAt);
    }

    public function isAccessTokenBlacklisted(string $token): bool
    {
        return $this->isTokenBlacklisted($token);
    }

    public function cleanupExpiredTokens(): void
    {
        $this->cleanExpiredTokens();
    }
}
