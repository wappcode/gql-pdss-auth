<?php

namespace GPDAuth\Models;

/**
 * Interface para servicios de autenticacion.
 * Define los metodos necesarios para manejar la autenticacion de usuarios.
 */
interface AuthServiceInterface
{
    public function login(string $username, string $password, string $grantType);
    public function logout(): void;
    public function getAuthenticatedUser(): ?AuthenticatedUserInterface;
}
