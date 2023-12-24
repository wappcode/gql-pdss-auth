<?php

namespace AppModule;

use Exception;
use GPDAuth\Services\AuthService;
use GPDCore\Library\AbstractModule;
use GPDCore\Library\GQLException;
use GPDCore\Library\IContextService;
use GraphQL\Type\Definition\Type;

class AppModule extends AbstractModule
{

    /**
     * Array con la configuración del módulo
     *
     * @return array
     */
    function getConfig(): array
    {
        return require(__DIR__ . '/../config/module.config.php');
    }
    function getServicesAndGQLTypes(): array
    {
        return [
            'invokables' => [],
            'factories' => [],
            'aliases' => []
        ];
    }
    /**
     * Array con los resolvers del módulo
     *
     * @return array array(string $key => callable $resolver)
     */
    function getResolvers(): array
    {
        return [];
    }
    /**
     * Array con los graphql Queries del módulo
     *
     * @return array
     */
    function getQueryFields(): array
    {
        return [
            'echo' =>  [
                'type' => Type::nonNull(Type::string()),
                'args' => [
                    'message' => Type::nonNull(Type::string())
                ],

                'resolve' => function ($root, $args) {
                    return $args["message"];
                }
            ],
            'echoProtected' => [
                'type' => Type::nonNull(Type::string()),
                'args' => [
                    'message' => Type::nonNull(Type::string()),
                ],
                'resolve' => function ($root, $args, IContextService $context, $info) {
                    /** @var AuthService */
                    $auth = $context->getServiceManager()->get(AuthService::class);
                    if (!$auth->isSigned()) {
                        throw new GQLException("No autorizado");
                    }
                    $user = $auth->getUser();
                    $msg = $args["message"];
                    $message = sprintf("%s -> Usuario: %s - %s", $msg, $user->getFullName(), $user->getUsername());
                    return $message;
                }
            ]
        ];
    }
    /**
     * Array con los graphql mutations del módulo
     *
     * @return array
     */
    function getMutationFields(): array
    {
        return [];
    }
}
