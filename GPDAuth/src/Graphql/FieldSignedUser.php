<?php

namespace GPDAuth\Graphql;

use Exception;
use GPDAuth\Entities\User;
use GPDAuth\Library\NoSignedException;
use GPDAuth\Services\AuthService;
use GPDCore\Library\GQLException;
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
            "resolve" => $proxyResolve
        ];
    }
    private static function createResolve(): callable
    {
        return function ($root, array $args, IContextService $context, $info) {
            /** @var AuthService */
            $auth = $context->getServiceManager()->get(AuthService::class);
            $user = $auth->getUser();
            if (empty($user)) {
                throw new NoSignedException();
            }
            $permissions = $auth->getPermissions();
            $token = $auth->getCurrentJWT();
            return [
                "user" => $user,
                "permissions" => $permissions,
                "jwt" => $token
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
