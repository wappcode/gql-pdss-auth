<?php

namespace GPDAuthJWT\Services;

use GPDAuth\Contracts\AuthenticatedUserInterface;
use GPDAuth\Models\AuthenticatedUser;
use GPDAuth\Models\AuthenticatedUserType;
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
        $username = $payload['iss'] . '|' . $payload['sub'];
        // Para usuarios humanos, se pueden mapear roles y permisos adicionales desde la base de datos si es necesario, usando el sub o el azp como identificador
        $authenticatedUser = (new AuthenticatedUser())
            ->setType(AuthenticatedUserType::EXTERN_USER)
            ->setId($username)
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
    public function getM2MUserFromPayload(array $payload, array $allowedPermissions = []): ?\GPDAuth\Contracts\AuthenticatedUserInterface
    {
        $authenticatedUser = null;
        // M2M solo tiene permisos de recurso basados en scopes, no roles ni datos de usuario
        $consumerId = $this->apiConsumerRepository->getConsumerIdFromJwtPayload($payload);
        $consumerName = $this->apiConsumerRepository->getConsumerName($consumerId);
        $authenticatedUser = (new AuthenticatedUser())
            ->setFullName($consumerName)
            ->setType(AuthenticatedUserType::API_CLIENT)
            ->setId($consumerId)
            ->setUsername($payload['iss'] . '|' . $payload['azp'])
            ->setFullName($payload['iss'] . '|' . $payload['azp'])
            ->setRoles([])
            ->setPermissions($allowedPermissions);

        return $authenticatedUser;
    }
}
