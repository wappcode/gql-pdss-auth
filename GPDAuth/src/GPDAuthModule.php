<?php

namespace GPDAuth;

use App\Middleware\AuthMiddleware;
use GPDAuth\Graphql\FieldLogin;
use GPDAuth\Services\AuthService;
use GPDAuth\Graphql\ResolversRole;
use GPDAuth\Graphql\ResolversUser;
use GPDAuth\Graphql\FieldSignedUser;
use GPDAuth\Graphql\ResolversPermission;
use Laminas\ServiceManager\ServiceManager;
use GPDAuth\Graphql\TypeSessionDataPermission;
use GPDAuth\Models\AuthenticatedUserInterface;
use GPDAuth\Models\AuthServiceInterface;
use GPDAuth\Models\UserRepositoryInterface;
use GPDAuth\Services\AuthSessionService;
use GPDAuth\Services\UserRepository;
use GPDCore\Core\AbstractModule;

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




    function getServices(): array
    {
        $context = $this->getAppContext();
        return [
            'invokables' => [
                TypeSessionDataPermission::NAME => TypeSessionDataPermission::class
            ],
            'factories' => [

                UserRepositoryInterface::class => function (ServiceManager $sm) use ($context) {
                    $entityManager = $context->getEntityManager();
                    return new UserRepository($entityManager);
                },
                AuthServiceInterface::class => function (ServiceManager $sm) use ($context) {
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
    function getMiddlewares(): array
    {
        return [];
        $authService = $this->getAppContext()->getServiceManager()->get(AuthServiceInterface::class);
        return [new AuthMiddleware($authService, identityKey: AuthenticatedUserInterface::class, exitUnauthenticated: false)];
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
