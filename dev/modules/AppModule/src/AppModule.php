<?php

namespace AppModule;

use GPDAuth\Graphql\AuthResolverGuardFactory;
use GPDAuth\Contracts\AuthServiceInterface;
use GPDCore\Contracts\AppContextInterface;
use GPDCore\Core\AbstractModule;
use GPDCore\Exceptions\GQLException;
use GPDCore\Graphql\ResolverPipelineFactory;

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
    function getServices(): array
    {
        return [
            'invokables' => [],
            'factories' => [],
            'aliases' => []
        ];
    }
    function getMiddlewares(): array
    {
        return [];
    }
    function getTypes(): array
    {
        return [];
    }
    function getRoutes(): array
    {
        return [];
    }
    /**
     * Array con los resolvers del módulo
     *
     * @return array array(string $key => callable $resolver)
     */
    function getResolvers(): array
    {
        $echoResolve = fn($root, $args) => $args["message"];
        $proxyEcho1 = fn($resolver) => function ($root, $args, AppContextInterface $context, $info) use ($resolver) {

            /** @var AuthServiceInterface */
            $authService = $context->getServiceManager()->get(AuthServiceInterface::class);
            $user = $authService->getAuthenticatedUser();
            if (!$user) {
                return $resolver($root, $args, $context, $info);
            }
            $msg = $resolver($root, $args, $context, $info);
            $message = sprintf("%s -> Usuario: %s", $msg, $user->getUsername(), $user->getUsername());
            return $message;
        };
        return [
            "Query::echo" => $echoResolve,
            'Query::echoProtected' => ResolverPipelineFactory::createPipeline($echoResolve, [
                // pipeline va en orden inverso al de ejecución
                ResolverPipelineFactory::createWrapper($proxyEcho1),
                AuthResolverGuardFactory::requireAuthenticated(),
            ]),
        ];
    }
}
