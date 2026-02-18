<?php

namespace GPDAuth\Services;

use GPDAuth\Library\InvalidUserException;
use GPDAuth\Models\AuthenticatedUserInterface;
use GPDAuth\Models\UserRepositoryInterface;

@session_start();
class AuthSessionService extends AbstractAuthService
{

    private UserRepositoryInterface $userRepository;
    private string $sessionKey;

    public function __construct(
        UserRepositoryInterface $userRepository,
        string $sessionKey = 'gpdauth_session_id'
    ) {
        $this->userRepository = $userRepository;
        $this->sessionKey = $sessionKey;
    }

    public function login(string $username, string $password, string $grantType): void
    {
        $this->authenticatedUser = $this->userRepository->validateCredentials($username, $password);
        if (!($this->authenticatedUser instanceof AuthenticatedUserInterface)) {
            throw new InvalidUserException('Invalid username and password or inactive user');
        }
        $this->setSession($this->authenticatedUser->getId(), $grantType);
        $this->userRepository->updateLastAccess($this->authenticatedUser);
    }
    public function logout(): void
    {
        $_SESSION[$this->sessionKey]    = null;
        $this->authenticatedUser = null;
    }

    public function setSession($userId, $grant): void
    {
        $_SESSION[$this->sessionKey]["identifier"] = $userId ?? null;
        $_SESSION[$this->sessionKey]["grant"] = $grant ?? null;
    }

    public function getAuthenticatedUser(): ?AuthenticatedUserInterface
    {
        if (!($this->authenticatedUser instanceof AuthenticatedUserInterface)) {
            $userId = $_SESSION[$this->sessionKey]["identifier"] ?? null;
            if ($userId !== null) {
                $this->authenticatedUser = $this->userRepository->findById($userId);
            }
        }
        return $this->authenticatedUser ?? null;
    }
}
