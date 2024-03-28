<?php

namespace GPDAuth\Graphql;

use GPDAuth\Library\NoSignedException;
use GPDAuth\Models\AuthSessionPermission;
use GPDAuth\Services\AuthService;
use GPDCore\Library\IContextService;

class FieldSignedUser
{

    /**
     * @var IContextService
     */
    protected $context;

    public static function get(IContextService $context, ?callable $proxy)
    {
        $resolve = FieldSignedUser::createResolve();
        $proxyResolve = is_callable($proxy) ? $proxy($resolve) : $resolve;
        $types = $context->getTypes();
        $serviceManager = $context->getServiceManager();
        $sessionDataType = $serviceManager->get(TypeFactorySessionData::NAME);
        return [
            "type" => $sessionDataType,
            "resolve" => $proxyResolve,
            "description" => "Recover session data of signed user"
        ];
    }
    private static function createResolve(): callable
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
            $token = $auth->getNewJWT();
            return [
                'user' => $user,
                'permissions' => $permissions,
                'roles' => $data["roles"] ?? [],
                'jwt' => $token
            ];
        };
    }

    private function __construct(IContextService $context)
    {
    }
    private function __clone()
    {
    }
}
