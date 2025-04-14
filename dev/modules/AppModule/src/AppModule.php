<?php

namespace AppModule;

use GPDAuth\Services\AuthService;
use GPDCore\Library\AbstractModule;
use GPDCore\Library\GQLException;
use GPDCore\Library\IContextService;

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
    function getSchema(): string
    {
        return file_get_contents(__DIR__ . "/../config/app-schema.graphql");
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
        return [
            'Query::echo' => fn($root, $args) => $args["message"],
            'Query::echoProtected' => function ($root, $args, IContextService $context, $info) {
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
        ];
    }
}
