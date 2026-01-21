<?php

namespace GPDAuth\Library;

class PasswordManager
{
    // Algoritmos de hash seguros (recomendados)
    const ARGON2ID = 'argon2id';
    const BCRYPT = 'bcrypt';
    
    // Algoritmos legacy (deprecados, solo para compatibilidad)
    const SHA256 = 'sha256';
    const SHA1 = 'sha1';
    const MD5 = 'md5';

    /**
     * Codifica una contraseña usando el algoritmo especificado
     * 
     * @param string $password La contraseña a codificar
     * @param string|null $salt Salt para algoritmos legacy (ignorado para bcrypt/argon2id)
     * @param string|null $hashAlgorithm Algoritmo a usar (default: argon2id)
     * @param array $options Opciones específicas del algoritmo
     * @return string Hash de la contraseña
     */
    public static function encode(string $password, ?string $salt = null, ?string $hashAlgorithm = null, array $options = []): string
    {
        $algo = $hashAlgorithm ?? static::ARGON2ID;
        
        switch ($algo) {
            case static::ARGON2ID:
                return static::encodeArgon2id($password, $options);
                
            case static::BCRYPT:
                return static::encodeBcrypt($password, $options);
                
            // Algoritmos legacy (deprecados)
            case static::SHA256:
            case static::SHA1:
            case static::MD5:
                if ($salt) $password = $password . $salt;
                return hash($algo, $password);
                
            default:
                throw new \InvalidArgumentException("Algoritmo de hash no soportado: {$algo}");
        }
    }

    /**
     * Verifica si una contraseña coincide con su hash
     * 
     * @param string $password Contraseña en texto plano
     * @param string $hash Hash almacenado
     * @param string|null $salt Salt (solo para algoritmos legacy)
     * @param string|null $hashAlgorithm Algoritmo usado (se detecta automáticamente si no se especifica)
     * @return bool True si la contraseña es correcta
     */
    public static function verify(string $password, string $hash, ?string $salt = null, ?string $hashAlgorithm = null): bool
    {
        // Si no se especifica algoritmo, intentar detectarlo automáticamente
        if ($hashAlgorithm === null) {
            $hashAlgorithm = static::detectHashAlgorithm($hash);
        }
        
        switch ($hashAlgorithm) {
            case static::ARGON2ID:
                return password_verify($password, $hash);
                
            case static::BCRYPT:
                return password_verify($password, $hash);
                
            // Algoritmos legacy
            case static::SHA256:
            case static::SHA1:
            case static::MD5:
                $expectedHash = static::encode($password, $salt, $hashAlgorithm);
                return hash_equals($hash, $expectedHash);
                
            default:
                throw new \InvalidArgumentException("Algoritmo de hash no soportado: {$hashAlgorithm}");
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
     * @return string
     */
    private static function detectHashAlgorithm(string $hash): string
    {
        // Argon2id hashes start with $argon2id$
        if (strpos($hash, '$argon2id$') === 0) {
            return static::ARGON2ID;
        }
        
        // Bcrypt hashes start with $2y$ (or similar variants)
        if (preg_match('/^\$2[axy]?\$/', $hash)) {
            return static::BCRYPT;
        }
        
        // Detectar por longitud para algoritmos legacy
        switch (strlen($hash)) {
            case 32:
                return static::MD5;
            case 40:
                return static::SHA1;
            case 64:
                return static::SHA256;
            default:
                throw new \InvalidArgumentException("No se pudo detectar el algoritmo del hash");
        }
    }

    /**
     * Verifica si un hash necesita ser rehaseado (para migración de algoritmos)
     * 
     * @param string $hash
     * @param string $algorithm
     * @param array $options
     * @return bool
     */
    public static function needsRehash(string $hash, string $algorithm = self::ARGON2ID, array $options = []): bool
    {
        switch ($algorithm) {
            case static::ARGON2ID:
                $defaultOptions = [
                    'memory_cost' => 65536,
                    'time_cost' => 4,
                    'threads' => 3,
                ];
                $options = array_merge($defaultOptions, $options);
                return password_needs_rehash($hash, PASSWORD_ARGON2ID, $options);
                
            case static::BCRYPT:
                $defaultOptions = ['cost' => 12];
                $options = array_merge($defaultOptions, $options);
                return password_needs_rehash($hash, PASSWORD_BCRYPT, $options);
                
            default:
                // Para algoritmos legacy, siempre recomendar rehashing a algoritmo seguro
                $currentAlgo = static::detectHashAlgorithm($hash);
                return in_array($currentAlgo, [static::SHA256, static::SHA1, static::MD5]);
        }
    }

    /**
     * Crea un salt para algoritmos legacy (deprecado)
     * 
     * @param string $hashAlgorithm
     * @return string
     * @deprecated Use Argon2id o Bcrypt que manejan el salt automáticamente
     */
    public static function createSalt(string $hashAlgorithm = 'sha256'): string
    {
        $randomkey = uniqid('salt', true);
        return hash($hashAlgorithm, $randomkey);
    }
}
