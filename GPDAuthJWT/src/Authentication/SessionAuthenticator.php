<?php

namespace GPDAuthJWT\Authentication;

use GPDAuth\Contracts\AuthenticatedUserInterface;
use GPDAuth\Contracts\UserRepositoryInterface;
use GPDAuth\Library\NoSignedException;
use GPDAuthJWT\Contracts\SessionAuthenticatorInterface;

class SessionAuthenticator implements SessionAuthenticatorInterface
{


    public function __construct(private UserRepositoryInterface $userRepository) {}
    /**
     * Autentica una sesión de usuario basada en una clave de sesión (session key).
     * @param string $sessionKey
     * @return AuthenticatedUserInterface
     * @throws NoSignedException
     */
    public function authenticate(string $sessionKey): AuthenticatedUserInterface
    {

        $userId = $_SESSION[$sessionKey]["identifier"] ?? null;
        if ($userId === null) {
            throw new NoSignedException();
        }
        $authenticatedUser = $this->userRepository->findById($userId);
        if ($authenticatedUser === null) {
            throw new NoSignedException();
        }
        return $authenticatedUser;
    }
}
