<?php

namespace GPDAuth\Library;

/**
 * Enum para algoritmos de firma JWT
 * Define los algoritmos soportados para firmar y verificar tokens JWT
 */
enum JwtAlgorithm: string
{
    /**
     * HMAC using SHA-256 (Simétrico)
     */
    case HS256 = 'HS256';
    
    /**
     * HMAC using SHA-384 (Simétrico)
     */
    case HS384 = 'HS384';
    
    /**
     * HMAC using SHA-512 (Simétrico)
     */
    case HS512 = 'HS512';
    
    /**
     * RSA using SHA-256 (Asimétrico)
     */
    case RS256 = 'RS256';
    
    /**
     * RSA using SHA-384 (Asimétrico)
     */
    case RS384 = 'RS384';
    
    /**
     * RSA using SHA-512 (Asimétrico)
     */
    case RS512 = 'RS512';
    
    /**
     * ECDSA using P-256 and SHA-256 (Asimétrico)
     */
    case ES256 = 'ES256';
    
    /**
     * ECDSA using P-384 and SHA-384 (Asimétrico)
     */
    case ES384 = 'ES384';
    
    /**
     * ECDSA using secp256k1 curve and SHA-256 (Asimétrico)
     */
    case ES256K = 'ES256K';

    /**
     * Obtiene el valor string del enum (para compatibilidad)
     * 
     * @return string
     */
    public function getValue(): string
    {
        return $this->value;
    }

    /**
     * Crea una instancia del enum desde un string
     * 
     * @param string $value
     * @return self
     * @throws \ValueError Si el valor no es válido
     */
    public static function fromString(string $value): self
    {
        return self::from($value);
    }

    /**
     * Crea una instancia del enum desde un string con fallback
     * 
     * @param string $value
     * @param self|null $default
     * @return self|null
     */
    public static function tryFromString(string $value, ?self $default = null): ?self
    {
        return self::tryFrom($value) ?? $default;
    }

    /**
     * Verifica si es un algoritmo simétrico (HMAC)
     * 
     * @return bool
     */
    public function isSymmetric(): bool
    {
        return in_array($this, [self::HS256, self::HS384, self::HS512]);
    }

    /**
     * Verifica si es un algoritmo asimétrico (RSA/ECDSA)
     * 
     * @return bool
     */
    public function isAsymmetric(): bool
    {
        return !$this->isSymmetric();
    }

    /**
     * Verifica si usa RSA
     * 
     * @return bool
     */
    public function isRSA(): bool
    {
        return in_array($this, [self::RS256, self::RS384, self::RS512]);
    }

    /**
     * Verifica si usa ECDSA
     * 
     * @return bool
     */
    public function isECDSA(): bool
    {
        return in_array($this, [self::ES256, self::ES384, self::ES256K]);
    }

    /**
     * Obtiene el algoritmo de hash usado
     * 
     * @return string
     */
    public function getHashAlgorithm(): string
    {
        return match($this) {
            self::HS256, self::RS256, self::ES256, self::ES256K => 'SHA256',
            self::HS384, self::RS384, self::ES384 => 'SHA384',
            self::HS512, self::RS512 => 'SHA512'
        };
    }

    /**
     * Obtiene la descripción del algoritmo
     * 
     * @return string
     */
    public function getDescription(): string
    {
        return match($this) {
            self::HS256 => 'HMAC con SHA-256 (simétrico, clave compartida)',
            self::HS384 => 'HMAC con SHA-384 (simétrico, clave compartida)',
            self::HS512 => 'HMAC con SHA-512 (simétrico, clave compartida)',
            self::RS256 => 'RSA con SHA-256 (asimétrico, clave pública/privada)',
            self::RS384 => 'RSA con SHA-384 (asimétrico, clave pública/privada)',
            self::RS512 => 'RSA con SHA-512 (asimétrico, clave pública/privada)',
            self::ES256 => 'ECDSA con P-256 y SHA-256 (asimétrico, curva elíptica)',
            self::ES384 => 'ECDSA con P-384 y SHA-384 (asimétrico, curva elíptica)',
            self::ES256K => 'ECDSA con secp256k1 y SHA-256 (asimétrico, Bitcoin/Ethereum)'
        };
    }

    /**
     * Obtiene el nivel de seguridad recomendado (1-5, donde 5 es más seguro)
     * 
     * @return int
     */
    public function getSecurityLevel(): int
    {
        return match($this) {
            self::HS256, self::RS256, self::ES256 => 3,
            self::HS384, self::RS384, self::ES384 => 4,
            self::HS512, self::RS512 => 5,
            self::ES256K => 4 // Seguro pero menos común
        };
    }

    /**
     * Verifica si requiere claves públicas/privadas
     * 
     * @return bool
     */
    public function requiresKeyPair(): bool
    {
        return $this->isAsymmetric();
    }

    /**
     * Obtiene los algoritmos recomendados para uso general
     * 
     * @return array<self>
     */
    public static function getRecommended(): array
    {
        return [self::HS256, self::HS384, self::RS256, self::ES256];
    }

    /**
     * Obtiene todos los algoritmos simétricos
     * 
     * @return array<self>
     */
    public static function getSymmetricAlgorithms(): array
    {
        return array_filter(self::cases(), fn(self $case) => $case->isSymmetric());
    }

    /**
     * Obtiene todos los algoritmos asimétricos
     * 
     * @return array<self>
     */
    public static function getAsymmetricAlgorithms(): array
    {
        return array_filter(self::cases(), fn(self $case) => $case->isAsymmetric());
    }

    /**
     * Obtiene el algoritmo por defecto recomendado
     * 
     * @return self
     */
    public static function getDefault(): self
    {
        return self::HS256;
    }
}