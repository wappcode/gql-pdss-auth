<?php

namespace GPDAuthJWT\Contracts;

use GPDAuth\Contracts\AuthenticatedUserInterface;

interface SessionAuthenticatorInterface
{
    /**
     * @throws \GPDAuth\Library\NoSignedException
     */
    public function authenticate(string $sessionKey): AuthenticatedUserInterface;
}
