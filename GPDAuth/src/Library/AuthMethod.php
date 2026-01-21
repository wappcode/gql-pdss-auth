<?php

namespace GPDAuth\Library;

/**
 * Enum para métodos de autenticación
 * Define las estrategias disponibles para autenticar usuarios
 */
enum AuthMethod: string
{
    /**
     * Autenticación solo por sesión
     */
    case Session = 'SESSION';
    
    /**
     * Autenticación solo por JWT
     */
    case Jwt = 'JWT';
    
    /**
     * Intenta JWT primero, si falla usa sesión como fallback
     */
    case JwtOrSession = 'JWT_OR_SESSION';
    
    /**
     * Intenta sesión primero, si falla usa JWT como fallback
     */
    case SessionOrJwt = 'SESSION_OR_JWT';

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
     * Verifica si usa sesión como método primario o secundario
     * 
     * @return bool
     */
    public function usesSession(): bool
    {
        return in_array($this, [self::Session, self::SessionOrJwt, self::JwtOrSession]);
    }

    /**
     * Verifica si usa JWT como método primario o secundario
     * 
     * @return bool
     */
    public function usesJwt(): bool
    {
        return in_array($this, [self::Jwt, self::JwtOrSession, self::SessionOrJwt]);
    }

    /**
     * Verifica si es un método híbrido (usa múltiples métodos)
     * 
     * @return bool
     */
    public function isHybrid(): bool
    {
        return in_array($this, [self::JwtOrSession, self::SessionOrJwt]);
    }

    /**
     * Obtiene el método primario
     * 
     * @return self
     */
    public function getPrimaryMethod(): self
    {
        return match($this) {
            self::JwtOrSession => self::Jwt,
            self::SessionOrJwt => self::Session,
            default => $this
        };
    }

    /**
     * Obtiene el método fallback si existe
     * 
     * @return self|null
     */
    public function getFallbackMethod(): ?self
    {
        return match($this) {
            self::JwtOrSession => self::Session,
            self::SessionOrJwt => self::Jwt,
            default => null
        };
    }
}