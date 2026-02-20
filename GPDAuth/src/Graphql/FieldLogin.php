<?php

namespace GPDAuth\Graphql;

use GPDAuth\Library\PermissionStringBuilder;
use GPDAuth\Models\AuthenticatedUserType;
use GPDAuth\Contracts\AuthServiceInterface;
use GPDCore\Contracts\AppContextInterface;
use GPDCore\Exceptions\GQLException;
use Throwable;

class FieldLogin
{


    public static function createResolve(): callable
    {
        return function ($root, array $args, AppContextInterface $context, $info) {
            $username = $args["username"] ?? '';
            $password = $args["password"] ?? '';
            /** @var AuthServiceInterface */
            $auth = $context->getServiceManager()->get(AuthServiceInterface::class);
            try {
                $auth->login($username, $password, AuthenticatedUserType::LOCAL_USER->value);
                $user = $auth->getAuthenticatedUser();
                $permissions = PermissionStringBuilder::formatAllowedPermissions($user);
                $userData = $user->toArray();
                $userData["permissions"] = $permissions;
                return $userData;
            } catch (Throwable $e) {
                throw new GQLException($e->getMessage(), "AUTH_LOGIN_INVALID_CREDENTIALS", 400);
            }
        };
    }
}
