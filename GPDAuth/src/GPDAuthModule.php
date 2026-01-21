<?php

namespace GPDAuth;

use GPDAuth\Graphql\FieldLogin;
use GPDAuth\Library\AuthConfigKey;
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
    function getSchema(): string
    {
        return file_get_contents(__DIR__ . '/../config/schema-auth.graphql');
    }
    function getServicesAndGQLTypes(): array
    {
        $context = $this->context;
        return [
            'invokables' => [
                TypeSessionDataPermission::NAME => TypeSessionDataPermission::class
            ],
            'factories' => [
                AuthService::class => function (ServiceManager $sm) {
                    $config = $this->context->getConfig();
                    $entityManager = $this->context->getEntityManager();
                    $authISSKey = $config->get(AuthConfigKey::AuthIssKey->value);
                    $authMethod = $config->get(AuthConfigKey::AuthMethodKey->value);
                    $authSecureKey = $config->get(AuthConfigKey::JwtSecureKey->value);
                    $authIssConfig = $config->get(AuthConfigKey::JwtIssConfig->value, []);
                    $authService = new AuthService(
                        $entityManager,
                        $authISSKey,
                        $authMethod,
                        $authSecureKey,
                        $authIssConfig
                    );

                    $authService->setJwtAlgoritm($config->get(AuthConfigKey::JwtAlgorithm->value));
                    $authService->setjwtExpirationTimeInSeconds($config->get(AuthConfigKey::JwtExpirationTime->value));
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
            'Query::login' => FieldLogin::createResolve(),
            'Query::getSessionData' => FieldSignedUser::createResolve(),

        ];
    }
}
