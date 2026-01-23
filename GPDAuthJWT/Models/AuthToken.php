<?php

namespace GPDAuthJWT\Models;

use DateTime;

/**
 * Modelo para representar tokens de autenticación
 */
class AuthToken
{
    private string $token;
    private string $userId;
    private DateTime $expiresAt;
    private DateTime $createdAt;
    private array $metadata;
    private string $type;

    public const TYPE_ACCESS = 'access';
    public const TYPE_REFRESH = 'refresh';

    public function __construct(
        string $token,
        string $userId,
        DateTime $expiresAt,
        string $type = self::TYPE_REFRESH,
        array $metadata = []
    ) {
        $this->token = $token;
        $this->userId = $userId;
        $this->expiresAt = $expiresAt;
        $this->type = $type;
        $this->metadata = $metadata;
        $this->createdAt = new DateTime();
    }

    /**
     * Verifica si el token ha expirado
     */
    public function isExpired(): bool
    {
        return $this->expiresAt <= new DateTime();
    }

    /**
     * Obtiene el token
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Obtiene el ID del usuario
     */
    public function getUserId(): string
    {
        return $this->userId;
    }

    /**
     * Obtiene la fecha de expiración
     */
    public function getExpiresAt(): DateTime
    {
        return $this->expiresAt;
    }

    /**
     * Obtiene la fecha de creación
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * Obtiene los metadatos del token
     */
    public function getMetadata(): array
    {
        return $this->metadata;
    }

    /**
     * Establece metadatos del token
     */
    public function setMetadata(array $metadata): self
    {
        $this->metadata = $metadata;
        return $this;
    }

    /**
     * Obtiene un valor específico de metadatos
     */
    public function getMetadataValue(string $key, $default = null)
    {
        return $this->metadata[$key] ?? $default;
    }

    /**
     * Establece un valor específico en metadatos
     */
    public function setMetadataValue(string $key, $value): self
    {
        $this->metadata[$key] = $value;
        return $this;
    }

    /**
     * Obtiene el tipo de token
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Establece el tipo de token
     */
    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    /**
     * Convierte el token a un array
     */
    public function toArray(): array
    {
        return [
            'token' => $this->token,
            'user_id' => $this->userId,
            'expires_at' => $this->expiresAt->format('Y-m-d H:i:s'),
            'created_at' => $this->createdAt->format('Y-m-d H:i:s'),
            'type' => $this->type,
            'metadata' => $this->metadata
        ];
    }

    /**
     * Crea una instancia desde un array
     */
    public static function fromArray(array $data): self
    {
        $token = new self(
            $data['token'],
            $data['user_id'],
            new DateTime($data['expires_at']),
            $data['type'] ?? self::TYPE_REFRESH,
            $data['metadata'] ?? []
        );

        if (isset($data['created_at'])) {
            $token->createdAt = new DateTime($data['created_at']);
        }

        return $token;
    }
}
