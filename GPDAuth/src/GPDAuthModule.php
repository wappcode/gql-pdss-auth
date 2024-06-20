<?php

namespace GPDAuth;

use GPDAuth\Graphql\FieldLogin;
use GPDAuth\Library\AuthConfig;
use GPDAuth\Services\AuthService;
use GPDAuth\Graphql\ResolversRole;
use GPDAuth\Graphql\ResolversUser;
use GPDCore\Library\AbstractModule;
use GPDAuth\Graphql\FieldSignedUser;
use GPDAuth\Graphql\ResolversPermission;
use Laminas\ServiceManager\ServiceManager;
use GPDAuth\Graphql\TypeFactorySessionData;
use GPDAuth\Graphql\TypeSessionDataPermission;
use GPDAuth\Graphql\TypeFactoryAuthSessionUser;

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
                TypeFactorySessionData::NAME => function (ServiceManager $sm) use ($context) {
                    $type = TypeFactorySessionData::create($context);
                    return $type;
                },
                TypeFactoryAuthSessionUser::NAME => function (ServiceManager $sm) use ($context) {
                    $type = TypeFactoryAuthSessionUser::create($context);
                    return $type;
                },

                AuthService::class => function (ServiceManager $sm) {
                    $config = $this->context->getConfig();
                    $entityManager = $this->context->getEntityManager();

                    $authService = new AuthService(
                        $entityManager,
                        $config->get(AuthConfig::AUTH_ISS_KEY),
                        $config->get(AuthConfig::AUTH_METHOD_KEY),
                    );

                    $authService->setJwtAlgoritm($config->get(AuthConfig::JWT_ALGORITHM_KEY));
                    $authService->setjwtExpirationTimeInSeconds($config->get(AuthConfig::JWT_EXPIRATION_TIME_KEY));
                    $authService->setJwtSecureKey($config->get(AuthConfig::JWT_SECURE_KEY));
                    $authService->initSession();
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
            'User::fullName' => ResolversUser::getFullNameResolve(),
            'User::roles' => ResolversUser::getRolesResolve($proxy = null),
            'Role::users' => ResolversRole::getUserResolve($proxy = null),
            'Permission::user' => ResolversPermission::getUserResolve($proxy = null),
            'Permission::role' => ResolversPermission::getRoleResolve($proxy = null),
            'Permission::resource' => ResolversPermission::getResourceResolve($proxy = null),

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
            "getSessionData" => FieldSignedUser::get($this->context, $proxy = null)
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
