<?php

namespace GPDAuthJWT\Contracts;

use GPDAuthJWT\DTO\AuthenticationResult;

interface JWTAuthenticatorInterface
{
    /**
     * Authenticate a JWT and return the authentication result.
     *
     * @param string $jwt
     * @return AuthenticationResult
     * @throws \RuntimeException if the JWT is invalid or cannot be authenticated
     */
    public function authenticate(string $jwt): AuthenticationResult;
}
