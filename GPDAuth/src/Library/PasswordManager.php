<?php

namespace GPDAuth\Library;

use GPDAuth\Enums\HashAlgorithm;
use InvalidArgumentException;

class PasswordManager
{

    /**
     * Codifica una contraseña usando el algoritmo especificado
     * 
     * @param string $password La contraseña a codificar
     * @param string|null $salt Salt para algoritmos legacy (ignorado para bcrypt/argon2id)
     * @param HashAlgorithm|string|null $hashAlgorithm Algoritmo a usar (default: argon2id)
     * @param array $options Opciones específicas del algoritmo
     * @return string Hash de la contraseña
     */
    public static function encode(string $password, ?string $salt = null, HashAlgorithm|string|null $hashAlgorithm = null, array $options = []): string
    {
        // Convertir string legacy a enum si es necesario
        if (is_string($hashAlgorithm)) {
            $hashAlgorithm = HashAlgorithm::fromString($hashAlgorithm);
        }

        $algo = $hashAlgorithm ?? HashAlgorithm::getDefault();

        switch ($algo) {
            case HashAlgorithm::Argon2id:
                return static::encodeArgon2id($password, $options);

            case HashAlgorithm::Bcrypt:
                return static::encodeBcrypt($password, $options);

                // Algoritmos legacy (deprecados)
            case HashAlgorithm::Sha256:
            case HashAlgorithm::Sha1:
            case HashAlgorithm::Md5:
                if ($salt) $password = $password . $salt;
                return hash($algo->value, $password);

            default:
                throw new InvalidArgumentException("Algoritmo de hash no soportado: {$algo->value}");
        }
    }

    /**
     * Verifica si una contraseña coincide con su hash
     * 
     * @param string $password Contraseña en texto plano
     * @param string $hash Hash almacenado
     * @param string|null $salt Salt (solo para algoritmos legacy)
     * @param HashAlgorithm|string|null $hashAlgorithm Algoritmo usado (se detecta automáticamente si no se especifica)
     * @return bool True si la contraseña es correcta
     */
    public static function verify(string $password, string $hash, ?string $salt = null, HashAlgorithm|string|null $hashAlgorithm = null): bool
    {
        // Si no se especifica algoritmo, intentar detectarlo automáticamente
        if ($hashAlgorithm === null) {
            $hashAlgorithm = static::detectHashAlgorithm($hash);
        }

        // Convertir string legacy a enum si es necesario
        if (is_string($hashAlgorithm)) {
            $hashAlgorithm = HashAlgorithm::fromString($hashAlgorithm);
        }

        switch ($hashAlgorithm) {
            case HashAlgorithm::Argon2id:
                return password_verify($password, $hash);

            case HashAlgorithm::Bcrypt:
                return password_verify($password, $hash);

                // Algoritmos legacy
            case HashAlgorithm::Sha256:
            case HashAlgorithm::Sha1:
            case HashAlgorithm::Md5:
                $expectedHash = static::encode($password, $salt, $hashAlgorithm);
                return hash_equals($hash, $expectedHash);

            default:
                throw new InvalidArgumentException("Algoritmo de hash no soportado: {$hashAlgorithm->value}");
        }
    }

    /**
     * Codifica contraseña usando Argon2id
     * 
     * @param string $password
     * @param array $options
     * @return string
     */
    private static function encodeArgon2id(string $password, array $options = []): string
    {
        $defaultOptions = [
            'memory_cost' => 65536, // 64 MB
            'time_cost' => 4,       // 4 iterations
            'threads' => 3,         // 3 threads
        ];

        $options = array_merge($defaultOptions, $options);

        return password_hash($password, PASSWORD_ARGON2ID, $options);
    }

    /**
     * Codifica contraseña usando Bcrypt
     * 
     * @param string $password
     * @param array $options
     * @return string
     */
    private static function encodeBcrypt(string $password, array $options = []): string
    {
        $defaultOptions = [
            'cost' => 12, // Factor de costo (rounds)
        ];

        $options = array_merge($defaultOptions, $options);

        return password_hash($password, PASSWORD_BCRYPT, $options);
    }

    /**
     * Detecta el algoritmo de hash basado en el formato del hash
     * 
     * @param string $hash
     * @return HashAlgorithm
     */
    private static function detectHashAlgorithm(string $hash): HashAlgorithm
    {
        // Argon2id hashes start with $argon2id$
        if (strpos($hash, '$argon2id$') === 0) {
            return HashAlgorithm::Argon2id;
        }

        // Bcrypt hashes start with $2y$ (or similar variants)
        if (preg_match('/^\$2[axy]?\$/', $hash)) {
            return HashAlgorithm::Bcrypt;
        }

        // Detectar por longitud para algoritmos legacy
        switch (strlen($hash)) {
            case 32:
                return HashAlgorithm::Md5;
            case 40:
                return HashAlgorithm::Sha1;
            case 64:
                return HashAlgorithm::Sha256;
            default:
                throw new InvalidArgumentException("No se pudo detectar el algoritmo del hash");
        }
    }

    /**
     * Verifica si un hash necesita ser rehaseado (para migración de algoritmos)
     * 
     * @param string $hash
     * @param HashAlgorithm|string $algorithm
     * @param array $options
     * @return bool
     */
    public static function needsRehash(string $hash, HashAlgorithm|string $algorithm = null, array $options = []): bool
    {
        $algorithm = $algorithm ?? HashAlgorithm::getDefault();

        // Convertir string legacy a enum si es necesario
        if (is_string($algorithm)) {
            $algorithm = HashAlgorithm::fromString($algorithm);
        }

        switch ($algorithm) {
            case HashAlgorithm::Argon2id:
                $defaultOptions = [
                    'memory_cost' => 65536,
                    'time_cost' => 4,
                    'threads' => 3,
                ];
                $options = array_merge($defaultOptions, $options);
                return password_needs_rehash($hash, PASSWORD_ARGON2ID, $options);

            case HashAlgorithm::Bcrypt:
                $defaultOptions = ['cost' => 12];
                $options = array_merge($defaultOptions, $options);
                return password_needs_rehash($hash, PASSWORD_BCRYPT, $options);

            default:
                // Para algoritmos legacy, siempre recomendar rehashing a algoritmo seguro
                $currentAlgo = static::detectHashAlgorithm($hash);
                return $currentAlgo->isLegacy();
        }
    }

    /**
     * Crea un salt para algoritmos legacy (deprecado)
     * 
     * @param HashAlgorithm|string $hashAlgorithm
     * @return string
     * @deprecated Use Argon2id o Bcrypt que manejan el salt automáticamente
     */
    public static function createSalt(HashAlgorithm|string $hashAlgorithm = null): string
    {
        $hashAlgorithm = $hashAlgorithm ?? HashAlgorithm::Sha256;

        // Convertir string legacy a enum si es necesario
        if (is_string($hashAlgorithm)) {
            $hashAlgorithm = HashAlgorithm::fromString($hashAlgorithm);
        }

        $randomkey = uniqid('salt', true);
        return hash($hashAlgorithm->value, $randomkey);
    }
}
