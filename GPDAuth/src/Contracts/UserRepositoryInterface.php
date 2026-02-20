<?php

namespace GPDAuth\Contracts;


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
     * @return AuthenticatedUserInterface|null
     */
    public function validateCredentials(string $username, string $password): ?AuthenticatedUserInterface;

    /**
     * Actualiza el último acceso del usuario
     *
     * @param AuthenticatedUserInterface $user
     * @return void
     */
    public function updateLastAccess(AuthenticatedUserInterface $user): void;


    /**
     * Busca un usuario por su ID
     *
     * @param string $userId
     * @return AuthenticatedUserInterface|null
     */
    public function findById(string $userId): ?AuthenticatedUserInterface;
}
