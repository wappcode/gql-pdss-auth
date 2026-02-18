<?php

namespace GPDAuth\Graphql;

use GPDAuth\Library\NoSignedException;
use GPDAuth\Models\AuthServiceInterface;
use GPDAuth\Models\ResourcePermission;
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
            $permissions = array_map(function (ResourcePermission $permission) {
                if ($permission->getAccess() === 'DENY') {
                    return null;
                }
                $resource = $permission->getResource();
                $value = $permission->getValue();
                $scope = $permission->getScope();
                if (empty($scope)) {
                    return sprintf("%s:%s", $resource, $value);
                }
                return sprintf("%s:%s:%s", $resource, $value, $scope);
            }, $user->getPermissions());

            $permissions = array_filter($permissions, fn($permission) => !empty($permission));
            $user["permissions"] = $permissions;
            return $user;
        };
    }

    private function __construct() {}
    private function __clone() {}
}
