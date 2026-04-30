<?php

namespace GPDAuthJWT\Services;

use GPDAuth\Contracts\AuthenticatedUserInterface;
use GPDAuth\Models\AuthenticatedUser;
use GPDAuth\Enums\AuthenticatedUserType;
use GPDAuthJWT\Contracts\ApiConsumerRepositoryInterface;

class JWTUserRepository implements \GPDAuthJWT\Contracts\JWTUserRepositoryInterfaces
{

    public function __construct(private ApiConsumerRepositoryInterface $apiConsumerRepository) {}

    /**
     * Devuelve un usuario autenticado a partir de los datos del payload del JWT
     *
     * @param array $payload El payload decodificado del JWT
     * @return AuthenticatedUserInterface|null El usuario autenticado o null si no se encuentra
     */
    public function getUserFromPayload(array $payload, array $allowedRoles = []): ?AuthenticatedUserInterface
    {
        $authenticatedUser = null;
        $subjectIdentifier = $payload['iss'] . '|' . $payload['sub'];
        // En JWT federado, 'iss+sub' es el identificador estable; username puede variar o repetirse entre IdPs.
        $username = $payload['preferred_username'] ?? $payload['email'] ?? $subjectIdentifier;
        // Para usuarios humanos, se pueden mapear roles y permisos adicionales desde la base de datos si es necesario, usando el sub o el azp como identificador
        $authenticatedUser = (new AuthenticatedUser())
            ->setType(AuthenticatedUserType::EXTERN_USER)
            ->setId($subjectIdentifier)
            ->setUsername($username)
            ->setFullName($payload["name"] ?? $username)
            ->setEmail($payload['email'] ?? null)
            ->setFirstName($payload['given_name'] ?? null)
            ->setLastName($payload['family_name'] ?? null)
            ->setPicture($payload['picture'] ?? null)
            ->setRoles($allowedRoles)
            ->setPermissions([]);
        return $authenticatedUser;
    }
    /**
     * Devuelve un usuario autenticado a partir de los datos del payload del JWT
     *
     * @param array $payload El payload decodificado del JWT
     * @return AuthenticatedUserInterface|null El usuario autenticado o null si no se encuentra
     */
    public function getM2MUserFromPayload(array $payload, array $allowedPermissions = [], array $roles = []): ?AuthenticatedUserInterface
    {
        $authenticatedUser = null;
        // M2M solo tiene permisos de recurso basados en scopes, no roles ni datos de usuario
        $consumerId = $this->apiConsumerRepository->getConsumerIdFromJwtPayload($payload);
        $consumerName = $this->apiConsumerRepository->getConsumerName($consumerId);
        $username = $payload["client_id"] ?? $payload['azp'] ?? $consumerId;
        $authenticatedUser = (new AuthenticatedUser())
            ->setType(AuthenticatedUserType::API_CLIENT)
            ->setId($consumerId)
            ->setUsername($username)
            ->setFullName($consumerName)
            ->setFirstName($consumerName)
            ->setRoles($roles)
            ->setPermissions($allowedPermissions);

        return $authenticatedUser;
    }
}
