<?php

namespace GPDAuth;

use GPDAuth\Graphql\FieldLogin;
use GPDAuth\Graphql\FieldSignedUser;
use GPDAuth\Graphql\ResolversUser;
use GPDAuth\Graphql\TypeFactoryAuthSession;
use GPDAuth\Graphql\TypeFactorySessionData;
use GPDAuth\Graphql\TypeSessionDataPermission;
use GPDAuth\Library\AuthConfig;
use GPDAuth\Library\IAuthService;
use GPDAuth\Services\AuthService;
use GPDCore\Library\AbstractModule;
use Laminas\ServiceManager\ServiceManager;

class GPDAuthModule extends AbstractModule
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
        $context = $this->context;
        return [
            'invokables' => [
                TypeSessionDataPermission::NAME => TypeSessionDataPermission::class
            ],
            'factories' => [
                TypeFactoryAuthSession::NAME => function (ServiceManager $sm) use ($context) {
                    $type = TypeFactoryAuthSession::create($context);
                    return $type;
                },
                TypeFactorySessionData::NAME => function (ServiceManager $sm) use ($context) {
                    $type = TypeFactorySessionData::create($context);
                    return $type;
                },

                AuthService::class => function (ServiceManager $sm) {
                    $config = $this->context->getConfig();
                    $entityManager = $this->context->getEntityManager();
                    $authService = new AuthService(
                        $entityManager,
                        $config->get(AuthConfig::AUTH_METHOD_KET),
                    );
                    $authService->setJwtAlgoritm($config->get(AuthConfig::JWT_ALGORITHM_KEY));
                    $authService->setjwtExpirationTimeInSeconds($config->get(AuthConfig::JWT_EXPIRATION_TIME_KEY));
                    $authService->setJwtSecureKey($config->get(AuthConfig::JWT_SECURE_KEY));
                    return $authService;
                },



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
        return [
            'User::fullName' => ResolversUser::getFullNameResolve()
        ];
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
