<?php

namespace GPDAuth\Library;

use GPDAuth\Models\AuthenticatedUserInterface;
use GPDAuth\Models\ResourcePermission;

class PermissionStringBuilder
{

    /**
     * Get the permissions of an authenticated user in string format.
     *
     * @param AuthenticatedUserInterface $user
     * @return array<string>
     */
    public static function formatAllowedPermissions(AuthenticatedUserInterface $user): array
    {
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
        return $permissions;
    }
}
