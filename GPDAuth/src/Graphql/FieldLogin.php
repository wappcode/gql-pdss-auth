<?php

namespace GPDAuth\Graphql;

use Exception;
use GPDAuth\Library\AuthJWTManager;
use GPDAuth\Services\AuthService;
use GPDCore\Library\GQLException;
use GraphQL\Type\Definition\Type;
use GPDCore\Library\IContextService;
use GPDAuth\Models\ResourcePermission;

class FieldLogin
{


    public static function createResolve(): callable
    {
        return function ($root, array $args, IContextService $context, $info) {
            $username = $args["username"] ?? '';
            $password = $args["password"] ?? '';
            /** @var AuthService */
            $auth = $context->getServiceManager()->get(AuthService::class);
            try {
                $auth->login($username, $password);
                $data = $auth->getSession();
                \$permissions = array_map(function (ResourcePermission \$permission) {
                    return $permission->toArray();
                }, $auth->getPermissions());
                $token = $auth->createNewJWTFromSession();
                $user = $auth->getUser()->toArray();
                $result = [
                    'user' => $user,
                    'permissions' => $permissions,
                    'roles' => $data["roles"] ?? [],
                    'jwt' => $token
                ];
                return $result;
            } catch (Exception $e) {
                throw new GQLException($e->getMessage(), "AUTH_LOGIN_400", 400);
            }
        };
    }


    private function __construct(IContextService $context) {}
    private function __clone() {}
}
