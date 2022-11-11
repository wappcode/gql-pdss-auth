<?php

namespace GPDAuth;

use AppModule\AppModule;
use GPDAuth\Graphql\FieldLogin;
use GPDAuth\Graphql\FieldSignedUser;
use GPDAuth\Services\AuthService;
use Laminas\ServiceManager\ServiceManager;

class GPDAuthModule extends AppModule
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
            'factories' => [
                AuthService::class => function (ServiceManager $sm) {
                    $entityManager = $this->context->getEntityManager();
                    return new AuthService($entityManager);
                }
            ],
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
            "login" => FieldLogin::get($this->context, $proxy = null),
            "signedUser" => FieldSignedUser::get($this->context, $proxy = null)
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
