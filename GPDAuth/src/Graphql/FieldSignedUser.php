<?php

namespace GPDAuth\Graphql;

use GPDAuth\Library\NoSignedException;
use GPDAuth\Library\PermissionStringBuilder;
use GPDAuth\Contracts\AuthServiceInterface;
use GPDCore\Contracts\AppContextInterface;

class FieldSignedUser
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
            $permissions = PermissionStringBuilder::formatAllowedPermissions($user);
            $userData = $user->toArray();
            $userData["permissions"] = $permissions;
            return $userData;
        };
    }

    private function __construct() {}
    private function __clone() {}
}
