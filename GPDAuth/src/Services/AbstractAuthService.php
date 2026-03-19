<?php

namespace GPDAuth\Services;

use Exception;

use GPDAuth\Contracts\AuthenticatedUserInterface;
use GPDAuth\Contracts\AuthServiceInterface;

@session_start();
abstract class AbstractAuthService implements AuthServiceInterface
{

    protected ?AuthenticatedUserInterface $authenticatedUser = null;

    /**
     *
     * 
     * Hay que inicializar sesion, roles y permisos
     * 
     * @param string $identifier (username o client_id)
     * @param string $password
     * @throws Exception
     */
    public abstract function login(string $identifier, string $password): void;

    public abstract function logout(): void;

    abstract public function getAuthenticatedUser(): ?AuthenticatedUserInterface;
}
