<?php

namespace AppModule;

use GPDAuth\Models\AuthServiceInterface;
use GPDAuth\Services\AuthService;
use GPDCore\Core\AbstractModule;
use GPDCore\Exceptions\GQLException as ExceptionsGQLException;
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
            'factories' => [
                AuthServiceInterface::class => function ($serviceManager) {
                    return new AuthService($this->context);
                },
            ],
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
    /**
     * Array con los resolvers del módulo
     *
     * @return array array(string $key => callable $resolver)
     */
    function getResolvers(): array
    {
        $echoResolve = fn($root, $args) => $args["message"];
        $proxyEcho1 = fn($resolver) => function ($root, $args, $context, $info) use ($resolver) {
            /** @var AuthService */
            $auth = $context->getServiceManager()->get(AuthService::class);
            if (!$auth->isSigned()) {
                throw new ExceptionsGQLException("No autorizado");
            }
            $user = $auth->getAuthenticatedUser();
            $msg = $args["message"];
            $message = sprintf("%s -> Usuario: %s - %s", $msg, $user->getFullName(), $user->getUsername());
            return $message;
            return   'Proxy 1 ' . $resolver($root, $args, $context, $info);
        };
        return [
            "Query::echo" => $echoResolve,
            'Query::echoProtected' => ResolverPipelineFactory::createPipeline($echoResolve, [
                // pipeline va en orden inverso al de ejecución
                ResolverPipelineFactory::createWrapper($proxyEcho1),
            ]),
        ];
    }
}
