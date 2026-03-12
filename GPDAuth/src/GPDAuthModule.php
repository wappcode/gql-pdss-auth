<?php

namespace GPDAuth;

use GPDAuth\Graphql\FieldLogin;
use GPDAuth\Graphql\ResolversRole;
use GPDAuth\Graphql\ResolversUser;
use GPDAuth\Graphql\FieldSignedUser;
use GPDAuth\Graphql\ResolversPermission;
use Laminas\ServiceManager\ServiceManager;
use GPDAuth\Middleware\AuthMiddleware;
use GPDAuth\Contracts\AuthenticatedUserInterface;
use GPDAuth\Contracts\AuthServiceInterface;
use GPDAuth\Contracts\UserRepositoryInterface;
use GPDAuth\Services\AuthSessionService;
use GPDAuth\Services\UserRepository;
use GPDAuthJWT\Authentication\SessionAuthenticator;
use GPDAuthJWT\Contracts\SessionAuthenticatorInterface;
use GPDCore\Core\AbstractModule;

/**
 * Este módulo se  para importar los resolvers principales
 */
class GPDAuthModule extends AbstractModule
{


    /**
     * Para graphql se recomienda configurar exitUnauthenticated en false,
     * Si exitUnauthenticated es true, el middleware de autenticación responderá con 401 si la autenticación falla, lo que es adecuado para rutas protegidas.
     * Si exitUnauthenticated es false, el middleware de autenticación permitirá que la solicitud continúe incluso si la autenticación falla, 
     * Por lo que la validación de autenticación y autorización se tiene que hacer en los resolvers o en los controllers, 
     * utilizando los datos del usuario autenticado que se encuentran en el atributo identity de request o en el servicio AuthServiceInterface.
     * @param boolean $exitUnauthenticated
     * @param array<string>  $publicRoutes Array de rutas públicas, ejemplo: ['/login', '/register']
     */
    public function __construct(private bool $exitUnauthenticated = false, private array $publicRoutes = []) {}

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

    function getRoutes(): array
    {
        return [];
    }


    function getServices(): array
    {
        $context = $this->getAppContext();
        return [
            'invokables' => [],
            'factories' => [

                UserRepositoryInterface::class => function (ServiceManager $sm) use ($context) {
                    $entityManager = $context->getEntityManager();
                    return new UserRepository($entityManager);
                },
                SessionAuthenticatorInterface::class => function (ServiceManager $sm) {
                    return new SessionAuthenticator(
                        $sm->get(UserRepositoryInterface::class)
                    );
                },
                AuthServiceInterface::class => function (ServiceManager $sm) use ($context) {
                    // Crear repositorios
                    $userRepository = $sm->get(UserRepositoryInterface::class);
                    $sessionAuthenticator = $sm->get(SessionAuthenticatorInterface::class);

                    // Crear servicio de autenticación
                    $authService = new AuthSessionService(
                        $userRepository,
                        $sessionAuthenticator,
                    );

                    return $authService;
                },



            ],
            'aliases' => []
        ];
    }
    function getMiddlewares(): array
    {
        $authService = $this->getAppContext()->getServiceManager()->get(AuthServiceInterface::class);
        return [new AuthMiddleware(
            $authService,
            identityKey: AuthenticatedUserInterface::class,
            exitUnauthenticated: $this->exitUnauthenticated,
            publicRoutes: $this->publicRoutes
        )];
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
