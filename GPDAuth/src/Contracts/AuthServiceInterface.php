<?php

namespace GPDAuth\Contracts;

/**
 * Interface para servicios de autenticacion.
 * Define los metodos necesarios para manejar la autenticacion de usuarios.
 */
interface AuthServiceInterface
{
    public function login(string $username, string $password);
    public function logout(): void;
    public function getAuthenticatedUser(): ?AuthenticatedUserInterface;
}
