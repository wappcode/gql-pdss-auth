<?php

namespace GPDAuthJWT\DTO;

use GPDAuth\Contracts\AuthenticatedUserInterface;

final class AuthenticationResult
{
    public function __construct(
        private AuthenticatedUserInterface $authenticatedUser,
        private array $payload,
        private array $header = []
    ) {}

    public function getAuthenticatedUser(): AuthenticatedUserInterface
    {
        return $this->authenticatedUser;
    }

    public function getPayload(): array
    {
        return $this->payload;
    }

    public function getHeader(): array
    {
        return $this->header;
    }
}
