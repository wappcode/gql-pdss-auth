<?php

namespace GPDAuth\Models;

use GPDAuth\Entities\User;

/**
 * Interface para el repositorio de usuarios
 */
interface UserRepositoryInterface
{


    /**
     * Valida las credenciales de un usuario
     *
     * @param string $username
     * @param string $password
     * @return AuthenticatedUser|null
     */
    public function validateCredentials(string $username, string $password): ?AuthenticatedUser;

    /**
     * Actualiza el último acceso del usuario
     *
     * @param AuthenticatedUser $user
     * @return void
     */
    public function updateLastAccess(AuthenticatedUser $user): void;

    /**
     * Busca un usuario por su ID
     *
     * @param string $userId
     * @return User|null
     */
    public function findById(string $userId): ?AuthenticatedUser;
}
