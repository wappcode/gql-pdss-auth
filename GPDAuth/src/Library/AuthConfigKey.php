<?php

namespace GPDAuth\Library;

/**
 * Enum para las claves de configuración de autenticación
 * Define todas las claves disponibles para configurar el sistema de autenticación
 */
enum AuthConfigKey: string
{
    /**
     * Clave del algoritmo JWT (ej: HS256)
     */
    case JwtAlgorithm = 'gpd_auth_jwt_algorithm_key';
    
    /**
     * Clave secreta para JWT
     */
    case JwtSecureKey = 'gpd_auth_jwt_secure_key';
    
    /**
     * Clave de sesión para autenticación
     */
    case AuthSessionKey = 'gpd_auth_session_key';
    
    /**
     * Método de autenticación (Session, JWT, etc)
     */
    case AuthMethodKey = 'gpd_auth_auth_method_key';
    
    /**
     * Clave del ISS (Issuer) para JWT
     */
    case AuthIssKey = 'gpd_auth_iss_key';
    
    /**
     * Tiempo de expiración por defecto para JWT
     */
    case JwtExpirationTime = 'gpd_auth_jwt_default_expiration_time';
    
    /**
     * Configuración de ISS para JWT
     */
    case JwtIssConfig = 'gpd_auth_jwt_iss_config';
    
    /**
     * Roles permitidos para un ISS específico
     */
    case AuthIssAllowedRoles = 'gpd_auth_jwt_iss_allowed_roes';

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
     * Verifica si es una clave relacionada con JWT
     * 
     * @return bool
     */
    public function isJwtRelated(): bool
    {
        return in_array($this, [
            self::JwtAlgorithm,
            self::JwtSecureKey,
            self::JwtExpirationTime,
            self::JwtIssConfig,
            self::AuthIssKey,
            self::AuthIssAllowedRoles
        ]);
    }

    /**
     * Verifica si es una clave relacionada con sesión
     * 
     * @return bool
     */
    public function isSessionRelated(): bool
    {
        return in_array($this, [
            self::AuthSessionKey,
            self::AuthMethodKey
        ]);
    }

    /**
     * Verifica si es una clave de configuración de seguridad crítica
     * 
     * @return bool
     */
    public function isSecurityCritical(): bool
    {
        return in_array($this, [
            self::JwtSecureKey,
            self::JwtAlgorithm,
            self::AuthIssAllowedRoles
        ]);
    }

    /**
     * Obtiene una descripción legible del propósito de la clave
     * 
     * @return string
     */
    public function getDescription(): string
    {
        return match($this) {
            self::JwtAlgorithm => 'Algoritmo utilizado para firmar JWT (ej: HS256, RS256)',
            self::JwtSecureKey => 'Clave secreta para firmar y verificar JWT',
            self::AuthSessionKey => 'Clave de sesión PHP para autenticación',
            self::AuthMethodKey => 'Método de autenticación (Session, JWT, híbrido)',
            self::AuthIssKey => 'Issuer (emisor) del token JWT',
            self::JwtExpirationTime => 'Tiempo de expiración por defecto para JWT en segundos',
            self::JwtIssConfig => 'Configuración de múltiples issuers JWT',
            self::AuthIssAllowedRoles => 'Roles permitidos para un issuer específico'
        };
    }

    /**
     * Obtiene todas las claves relacionadas con JWT
     * 
     * @return array<self>
     */
    public static function getJwtKeys(): array
    {
        return array_filter(self::cases(), fn(self $case) => $case->isJwtRelated());
    }

    /**
     * Obtiene todas las claves relacionadas con sesión
     * 
     * @return array<self>
     */
    public static function getSessionKeys(): array
    {
        return array_filter(self::cases(), fn(self $case) => $case->isSessionRelated());
    }

    /**
     * Obtiene todas las claves críticas de seguridad
     * 
     * @return array<self>
     */
    public static function getSecurityCriticalKeys(): array
    {
        return array_filter(self::cases(), fn(self $case) => $case->isSecurityCritical());
    }
}