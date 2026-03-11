<?php

namespace GPDAuthJWT\Contracts;

use GPDAuthJWT\DTO\AuthenticationResult;

interface JWTAuthenticatorInterface
{
    public function authenticate(string $jwt): AuthenticationResult;
}
