<?php

namespace GPDAuth\Enums;

/**
 * Enum para algoritmos de hash de contraseñas
 *
 * Define los algoritmos disponibles para el hash de contraseñas,
 * categorizándolos entre seguros y legacy (deprecados).
 */
enum HashAlgorithm: string
{
    // Algoritmos seguros (recomendados)
    case Argon2id = 'argon2id';
    case Bcrypt = 'bcrypt';

        // Algoritmos legacy (deprecados, solo para compatibilidad)
    case Sha256 = 'sha256';
    case Sha1 = 'sha1';
    case Md5 = 'md5';

    /**
     * Verifica si el algoritmo es seguro (recomendado para nuevos hashes)
     */
    public function isSecure(): bool
    {
        return in_array($this, [self::Argon2id, self::Bcrypt]);
    }

    /**
     * Verifica si el algoritmo es legacy (deprecado)
     */
    public function isLegacy(): bool
    {
        return !$this->isSecure();
    }

    /**
     * Obtiene los algoritmos seguros disponibles
     *
     * @return array<HashAlgorithm>
     */
    public static function getSecureAlgorithms(): array
    {
        return [self::Argon2id, self::Bcrypt];
    }

    /**
     * Obtiene los algoritmos legacy (deprecados)
     *
     * @return array<HashAlgorithm>
     */
    public static function getLegacyAlgorithms(): array
    {
        return [self::Sha256, self::Sha1, self::Md5];
    }

    /**
     * Crea una instancia del enum desde un string
     * Útil para mantener compatibilidad con código legacy
     *
     * @param string $algorithm
     * @return HashAlgorithm
     * @throws \InvalidArgumentException
     */
    public static function fromString(string $algorithm): HashAlgorithm
    {
        return match (strtolower($algorithm)) {
            'argon2id' => self::Argon2id,
            'bcrypt' => self::Bcrypt,
            'sha256' => self::Sha256,
            'sha1' => self::Sha1,
            'md5' => self::Md5,
            default => throw new \InvalidArgumentException("Algoritmo de hash no soportado: {$algorithm}")
        };
    }

    /**
     * Obtiene el algoritmo por defecto (más seguro disponible)
     */
    public static function getDefault(): HashAlgorithm
    {
        return self::Argon2id;
    }

    /**
     * Obtiene información de seguridad del algoritmo
     */
    public function getSecurityInfo(): array
    {
        return match ($this) {
            self::Argon2id => [
                'security_level' => 'high',
                'recommended' => true,
                'description' => 'Algoritmo más seguro, resistente a ataques GPU/ASIC'
            ],
            self::Bcrypt => [
                'security_level' => 'high',
                'recommended' => true,
                'description' => 'Algoritmo seguro, ampliamente soportado'
            ],
            self::Sha256 => [
                'security_level' => 'low',
                'recommended' => false,
                'description' => 'Legacy - vulnerable a ataques de fuerza bruta'
            ],
            self::Sha1 => [
                'security_level' => 'very_low',
                'recommended' => false,
                'description' => 'Legacy - criptográficamente roto'
            ],
            self::Md5 => [
                'security_level' => 'very_low',
                'recommended' => false,
                'description' => 'Legacy - criptográficamente roto'
            ]
        };
    }
}
