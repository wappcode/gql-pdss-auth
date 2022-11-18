<?php

namespace GPDAuth\Graphql;

use Exception;
use GPDAuth\Services\AuthService;
use GPDCore\Library\GQLException;
use GraphQL\Type\Definition\Type;
use GPDCore\Library\IContextService;
use GPDAuth\Graphql\TypeFactorySessionData;

class FieldLogin
{

    /**
     * @var IContextService
     */
    protected $context;

    public static function get(IContextService $context, ?callable $proxy)
    {
        $resolve = FieldLogin::createResolve();
        $proxyResolve = is_callable($proxy) ? $proxy($resolve) : $resolve;
        $serviceManager = $context->getServiceManager();
        $sessionDataType = $serviceManager->get(TypeFactorySessionData::NAME);
        return [
            "type" => $sessionDataType,
            "args" => [
                [
                    "name" => "username",
                    "type" => Type::nonNull(Type::string())
                ],
                [
                    "name" => "password",
                    "type" => Type::nonNull(Type::string())
                ],
            ],
            "resolve" => $proxyResolve
        ];
    }
    private static function createResolve(): callable
    {
        return function ($root, array $args, IContextService $context, $info) {
            $username = $args["username"] ?? '';
            $password = $args["password"] ?? '';
            /** @var AuthService */
            $auth = $context->getServiceManager()->get(AuthService::class);
            try {
                $auth->login($username, $password);
                $user = $auth->getUser();
                $permissions = $auth->getPermissions();
                $token = $auth->getCurrentJWT();
                $result = [
                    'user' => $user,
                    'permissions' => $permissions,
                    'jwt' => $token
                ];
                return $result;
            } catch (Exception $e) {
                throw new GQLException($e->getMessage(), "AUTH_LOGIN_400", 400);
            }
        };
    }


    private function __construct(IContextService $context)
    {
    }
    private function __clone()
    {
    }
    private function __wakeup()
    {
    }
}
