<?php

namespace GPDAuth\Graphql;

use GPDAuth\Library\NoSignedException;
use GPDAuth\Contracts\AuthServiceInterface;
use GPDCore\Contracts\AppContextInterface;

class FieldLogout
{

    public static function createResolve(): callable
    {
        return function ($root, array $args, AppContextInterface $context) {
            /** @var AuthServiceInterface */
            $auth = $context->getServiceManager()->get(AuthServiceInterface::class);
            $user = $auth->getAuthenticatedUser();
            if (empty($user)) {
                throw new NoSignedException();
            }
            $auth->logout();
            return true;
        };
    }

    private function __construct() {}
    private function __clone() {}
}
