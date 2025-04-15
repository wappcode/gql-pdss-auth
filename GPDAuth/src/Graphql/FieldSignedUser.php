<?php

namespace GPDAuth\Graphql;

use GPDAuth\Library\AuthJWTManager;
use GPDAuth\Library\NoSignedException;
use GPDAuth\Models\AuthSessionPermission;
use GPDAuth\Services\AuthService;
use GPDCore\Library\IContextService;

class FieldSignedUser
{

    public static function createResolve(): callable
    {
        return function ($root, array $args, IContextService $context, $info) {
            /** @var AuthService */
            $auth = $context->getServiceManager()->get(AuthService::class);
            $user = $auth->getUser()->toArray();
            if (empty($user)) {
                throw new NoSignedException();
            }
            $data = $auth->getSession();
            $permissions = array_map(function (AuthSessionPermission $permission) {
                return $permission->toArray();
            }, $auth->getPermissions());
            $token = AuthJWTManager::retriveJWT();
            return [
                'user' => $user,
                'permissions' => $permissions,
                'roles' => $data["roles"] ?? [],
                'jwt' => $token
            ];
        };
    }

    private function __construct(IContextService $context) {}
    private function __clone() {}
}
