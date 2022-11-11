<?php

namespace GPDAuth;

use AppModule\AppModule;
use GPDAuth\Graphql\FieldLogin;
use GPDAuth\Graphql\FieldSignedUser;
use GPDAuth\Library\AuthConfig;
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
                    $config = $this->context->getConfig();
                    $entityManager = $this->context->getEntityManager();
                    $authService = new AuthService(
                        $entityManager,
                        $config->get(AuthConfig::AUTH_SESSION_KEY),
                        $config->get(AuthConfig::JWT_SECURE_KEY),
                        $config->get(AuthConfig::JWT_ALGORITHM_KEY),
                        $config->get(AuthConfig::JWT_EXPIRATION_TIME_KEY),
                    );
                    return $authService;
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
