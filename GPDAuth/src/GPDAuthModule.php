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
use GPDAuth\Graphql\TypeSessionDataPermission;
use GPDAuth\Models\UserRepositoryInterface;
use GPDAuth\Services\AuthSessionService;

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

                UserRepositoryInterface::class => function (ServiceManager $sm) use ($context) {
                    $entityManager = $context->getEntityManager();
                    return new \GPDAuth\Services\UserRepository($entityManager);
                },
                AuthService::class => function (ServiceManager $sm) {
                    // Crear repositorios
                    $userRepository = $sm->get(UserRepositoryInterface::class);

                    // Crear servicio de autenticación
                    $authService = new AuthSessionService(
                        $userRepository,
                    );

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
