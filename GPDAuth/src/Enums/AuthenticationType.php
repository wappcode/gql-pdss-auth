<?php

namespace GPDAuth\Enums;

/**
 * Enum para los tipos de autenticación detectados en requests
 */
enum AuthenticationType: string
{
    case SESSION = 'session';
    case ACCESS_TOKEN = 'access_token';
    case REFRESH_TOKEN = 'refresh_token';
    case NONE = 'none';

    /**
     * Detecta el tipo de autenticación basado en los headers y sesión
     */
    public static function detect(): self
    {
        // Verificar Bearer token en headers
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return self::ACCESS_TOKEN;
        }

        // Verificar si hay sesión activa
        if (session_status() === PHP_SESSION_ACTIVE && !empty($_SESSION)) {
            return self::SESSION;
        }

        return self::NONE;
    }

    /**
     * Extrae el token del header Authorization
     */
    public static function extractBearerToken(): ?string
    {
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            return trim($matches[1]);
        }
        return null;
    }

    /**
     * Determina si el tipo requiere tokens
     */
    public function requiresToken(): bool
    {
        return in_array($this, [self::ACCESS_TOKEN, self::REFRESH_TOKEN]);
    }

    /**
     * Determina si el tipo usa sesión
     */
    public function usesSession(): bool
    {
        return $this === self::SESSION;
    }

    /**
     * Obtiene una descripción legible del tipo
     */
    public function getDescription(): string
    {
        return match ($this) {
            self::SESSION => 'Autenticación basada en sesión PHP',
            self::ACCESS_TOKEN => 'Autenticación con token de acceso JWT',
            self::REFRESH_TOKEN => 'Token de actualización',
            self::NONE => 'Sin autenticación detectada'
        };
    }
}
