<?php

namespace GPDAuthJWT\Contracts;

use GPDAuth\Contracts\AuthenticatedUserInterface;

interface JWTUserRepositoryInterfaces
{
    /**
     * Devuelve un usuario autenticado a partir de los datos del payload del JWT
     *
     * @param array $payload El payload decodificado del JWT
     * @return AuthenticatedUserInterface|null El usuario autenticado o null si no se encuentra
     */
    public function getUserFromPayload(array $payload, array $allowedRoles): ?AuthenticatedUserInterface;
    public function getM2MUserFromPayload(array $payload, array $allowedPermissions): ?AuthenticatedUserInterface;
}
