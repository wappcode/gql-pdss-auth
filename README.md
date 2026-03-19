# GPDAuth - Sistema de Autenticación y Autorización

Librería completa de autenticación y autorización para aplicaciones con WAppCore (GQLPDSS). Provee servicios de autenticación mediante sesión PHP y JWT con un sistema robusto de roles y permisos.

## 📋 Tabla de Contenidos

- [Instalación](#-instalación)
- [Configuración Básica](#-configuración-básica)
- [Uso Rápido](#-uso-rápido)
- [AuthService](#-authservice)
- [Sistema de Roles y Permisos](#-sistema-de-roles-y-permisos)
- [Protección de Resolvers GraphQL](#-protección-de-resolvers-graphql)
- [Middleware de Autenticación](#-middleware-de-autenticación)
- [JWT Authentication](#-jwt-authentication)
- [MUY IMPORTANTE: Unicidad de Username](#-muy-importante-unicidad-de-username)
- [API Reference](#-api-reference)

## 🚀 Instalación

### 1. Instalación via Composer

```bash
composer require wappcode/gql-pdss-auth
```

### 2. Configuración de Entidades Doctrine

Agregue las entidades del paquete a la configuración de Doctrine en `config/local.config.php`:

```php
<?php
return [
    'database' => [
        'connection' => [
            'driver' => 'pdo_mysql',
            'user' => 'usuario',
            'password' => 'password',
            'host' => 'localhost',
            'port' => 3306,
            'dbname' => 'app',
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci'
        ],
        "entity_paths" => [
           "GPDAuth\Entities"=> realpath(__DIR__ . "/../../vendor/wappcode/gql-pdss-auth/GPDAuth/src/Entities"),
            "GPDAuthJWT\Entities"=> realpath(__DIR__ . "/../../vendor/wappcode/gql-pdss-auth/GPDAuthJWT/src/Entities"),
        ]
    ]
];
```

### 3. Crear Esquema de Base de Datos

**CLI de Doctrine**
```bash
vendor/bin/doctrine orm:schema-tool:create
```

## ⚙️ Configuración Básica

### Integración con GQLPDSS

Configure el módulo en su aplicación (normalmente en `dev/public/index.php`):

```php
<?php
use GPDAuth\GPDAuthModule;

// Configuración básica (recomendada para GraphQL)
$app->addModules([
    new GPDAuthModule(
        exitUnauthenticated: false,  // Para GraphQL, permite validación granular por resolver
        publicRoutes: ['/login', '/register']  // Rutas que no requieren autenticación
    ),
    // Otros módulos...
    AppModule::class,
]);
```

### Configuración de Módulos

```php
<?php
// Para aplicaciones REST API tradicionales
new GPDAuthModule(
    exitUnauthenticated: true,   // Responde 401 si no está autenticado
    publicRoutes: ['/login', '/register', '/forgot-password']
);

// Para aplicaciones GraphQL (recomendado)
new GPDAuthModule(
    exitUnauthenticated: false,  // Permite validación a nivel de resolver
    publicRoutes: ['/login']
);
```

## 🏃‍♂️ Uso Rápido

### Autenticación Básica

```php
<?php
use GPDAuth\Contracts\AuthServiceInterface;

// En un resolver o controlador
$authService = $context->getServiceManager()->get(AuthServiceInterface::class);

// Login
try {
    $authService->login('username', 'password');
    echo "Login exitoso";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}

// Verificar si está autenticado
$user = $authService->getAuthenticatedUser();
if ($user) {
    echo "Usuario: " . $user->getUsername();
}

// Logout
$authService->logout();
```

### GraphQL Integration

```php
<?php
// En un módulo GraphQL
use GPDAuth\Graphql\AuthResolverGuardFactory;
use GPDCore\Graphql\ResolverPipelineFactory;

class AppModule extends AbstractModule
{
    function getResolvers(): array
    {
        $echoResolve = fn($root, $args) => $args["message"];
        
        return [
            // Resolver público
            "Query::echo" => $echoResolve,
            
            // Resolver protegido que requiere autenticación
            'Query::echoProtected' => ResolverPipelineFactory::createPipeline($echoResolve, [
                AuthResolverGuardFactory::requireAuthenticated(),
            ]),
            
            // Resolver que requiere rol específico
            'Query::adminOnly' => ResolverPipelineFactory::createPipeline($someResolver, [
                AuthResolverGuardFactory::requireRole('admin'),
            ]),
        ];
    }
}
```

## 🔐 AuthService

El `AuthServiceInterface` es el corazón del sistema de autenticación:

### Métodos Principales

```php
<?php
use GPDAuth\Contracts\AuthServiceInterface;

$auth = $context->getServiceManager()->get(AuthServiceInterface::class);

// Autenticación
$auth->login(string $username, string $password): void;
$auth->logout(): void;
$user = $auth->getAuthenticatedUser(): ?AuthenticatedUserInterface;
```

### Ejemplo Práctico en GraphQL

```php
<?php
// Resolver de login
public static function createLoginResolve(): callable
{
    return function ($root, array $args, AppContextInterface $context, $info) {
        $username = $args["username"] ?? '';
        $password = $args["password"] ?? '';
        
        /** @var AuthServiceInterface */
        $auth = $context->getServiceManager()->get(AuthServiceInterface::class);
        
        try {
            $auth->login($username, $password);
            $user = $auth->getAuthenticatedUser();
            
            return [
                'success' => true,
                'user' => $user->toArray(),
                'message' => 'Login exitoso'
            ];
        } catch (Throwable $e) {
            throw new GQLException('Credenciales inválidas', 'INVALID_CREDENTIALS');
        }
    };
}
```

## 👥 Sistema de Roles y Permisos

### Interfaz AuthenticatedUserInterface

```php
<?php
// Verificación de roles
$user->hasRole(string $role): bool;
$user->hasAnyRole(array $roles): bool;
$user->hasAllRoles(array $roles): bool;

// Verificación de permisos
$user->hasPermission(string $resource, string $permission, ?string $scope = null): bool;
$user->hasAnyPermission(array $resources, array $permissions, ?array $scopes = null): bool;
$user->hasAllPermissions(array $resources, array $permissions, ?array $scopes = null): bool;
```

### Ejemplos de Uso

```php
<?php
$user = $auth->getAuthenticatedUser();

// Verificar roles
if ($user->hasRole('admin')) {
    echo "Usuario es administrador";
}

if ($user->hasAnyRole(['editor', 'publisher'])) {
    echo "Usuario puede editar contenido";
}

// Verificar permisos específicos
if ($user->hasPermission('posts', 'CREATE')) {
    echo "Usuario puede crear posts";
}

// Permisos con scope
if ($user->hasPermission('posts', 'EDIT', 'OWNER')) {
    echo "Usuario puede editar sus propios posts";
}

if ($user->hasPermission('posts', 'EDIT', 'ALL')) {
    echo "Usuario puede editar cualquier post";
}

// Permisos múltiples
if ($user->hasAllPermissions(['posts', 'comments'], ['CREATE', 'EDIT'], ['ALL'])) {
    echo "Usuario tiene control completo sobre posts y comentarios";
}
```

## 🛡️ Protección de Resolvers GraphQL

### AuthResolverGuardFactory

Protege resolvers GraphQL con diferentes niveles de autorización:

```php
<?php
use GPDAuth\Graphql\AuthResolverGuardFactory;

// Requiere autenticación (cualquier usuario logueado)
AuthResolverGuardFactory::requireAuthenticated();

// Requiere rol específico
AuthResolverGuardFactory::requireRole('admin');

// Requiere cualquiera de los roles
AuthResolverGuardFactory::requireAnyRole(['editor', 'publisher']);

// Requiere todos los roles
AuthResolverGuardFactory::requireAllRoles(['staff', 'verified']);

// Requiere permiso específico
AuthResolverGuardFactory::requirePermission('posts', 'CREATE');

// Requiere permiso con scope
AuthResolverGuardFactory::requirePermission('posts', 'EDIT', 'OWNER');

// Requiere cualquier permiso de la lista
AuthResolverGuardFactory::requireAnyPermission(
    ['posts', 'pages'], 
    ['CREATE', 'EDIT'], 
    ['ALL', 'OWNER']
);

// Requiere todos los permisos
AuthResolverGuardFactory::requireAllPermissions(
    ['posts'], 
    ['CREATE', 'EDIT', 'DELETE'], 
    ['ALL']
);
```

### Ejemplo Completo de Protección

```php
<?php
class AppModule extends AbstractModule
{
    function getResolvers(): array
    {
        return [
            // Público
            'Query::login' => FieldLogin::createResolve(),
            
            // Solo usuarios autenticados
            'Query::profile' => ResolverPipelineFactory::createPipeline(
                $profileResolver,
                [AuthResolverGuardFactory::requireAuthenticated()]
            ),
            
            // Solo administradores
            'Mutation::deleteUser' => ResolverPipelineFactory::createPipeline(
                $deleteUserResolver,
                [AuthResolverGuardFactory::requireRole('admin')]
            ),
            
            // Editores o publicadores
            'Mutation::publishPost' => ResolverPipelineFactory::createPipeline(
                $publishResolver,
                [AuthResolverGuardFactory::requireAnyRole(['editor', 'publisher'])]
            ),
            
            // Permiso específico con scope
            'Mutation::editPost' => ResolverPipelineFactory::createPipeline(
                $editPostResolver,
                [AuthResolverGuardFactory::requirePermission('posts', 'EDIT', 'OWNER')]
            ),
        ];
    }
}
```

## 🔒 Middleware de Autenticación

### AuthMiddleware

El middleware valida automáticamente las solicitudes:

```php
<?php
// Configuración en GPDAuthModule
new GPDAuthModule(
    exitUnauthenticated: true,   // true: responde 401 si no autenticado
                                // false: continúa y permite validación granular
    publicRoutes: ['/login', '/register', '/forgot-password']
);
```

### Comportamiento del Middleware

- **exitUnauthenticated: true** - Ideal para APIs REST
  - Responde inmediatamente con 401 si no está autenticado
  - Protege todas las rutas excepto las públicas
  
- **exitUnauthenticated: false** - Ideal para GraphQL
  - Permite que las solicitudes continúen
  - AuthResolverGuardFactory maneja la validación por resolver

### Acceso al Usuario en Request

```php
<?php
// El middleware inyecta el usuario autenticado en el request
$request = $context->getContextAttribute(ServerRequestInterface::class);
$user = $request->getAttribute(AuthenticatedUserInterface::class);

if ($user instanceof AuthenticatedUserInterface) {
    echo "Usuario autenticado: " . $user->getUsername();
}
```

## 🎫 JWT Authentication

### Módulo GPDAuthJWT

Para autenticación JWT, use el módulo adicional:

```php
<?php
use GPDAuthJWT\GPDAuthJWTModule;

// Configurar junto con el módulo base
$app->addModules([
    new GPDAuthModule(exitUnauthenticated: false),
    new GPDAuthJWTModule(),
    AppModule::class,
]);
```

Internamente, `GPDAuthJWT` ahora centraliza la autenticación del token en un autenticador dedicado. El middleware JWT delega la validación, la resolución del usuario y la extracción del payload en `JWTAuthenticator`, expuesto mediante la interfaz `JWTAuthenticatorInterface`.

### Flujo de autenticación JWT

El flujo actual es:

1. `JwtAuthMiddleware` extrae el bearer token desde la request.
2. `JWTAuthenticatorInterface::authenticate(string $jwt)` valida firma, issuer, audience, expiración y tipo de token.
3. El autenticador resuelve el usuario autenticado humano o M2M.
4. El resultado se devuelve como un `AuthenticationResult`.
5. El middleware inyecta en la request:
   - `AuthenticatedUserInterface::class` con el usuario autenticado
   - `jwt_payload` con el payload ya validado

### Acceso al resultado de autenticación JWT

```php
<?php
use GPDAuth\Contracts\AuthenticatedUserInterface;
use Psr\Http\Message\ServerRequestInterface;

$request = $context->getContextAttribute(ServerRequestInterface::class);

$user = $request->getAttribute(AuthenticatedUserInterface::class);
$jwtPayload = $request->getAttribute('jwt_payload');

if ($user instanceof AuthenticatedUserInterface) {
    echo $user->getUsername();
}

if (is_array($jwtPayload)) {
    echo $jwtPayload['iss'] ?? '';
}
```

### Contrato del autenticador JWT

```php
<?php
use GPDAuthJWT\Contracts\JWTAuthenticatorInterface;
use GPDAuthJWT\DTO\AuthenticationResult;

interface JWTAuthenticatorInterface
{
    public function authenticate(string $jwt): AuthenticationResult;
}
```

### DTO AuthenticationResult

El DTO `AuthenticationResult` encapsula el resultado completo de la autenticación JWT:

```php
<?php
use GPDAuth\Contracts\AuthenticatedUserInterface;

final class AuthenticationResult
{
    public function getAuthenticatedUser(): AuthenticatedUserInterface;
    public function getPayload(): array;
    public function getHeader(): array;
}
```

### JWT vs Session

- **Session Auth**: Usa sesiones PHP, ideal para aplicaciones web tradicionales
- **JWT Auth**: Tokens sin estado, ideal para APIs y aplicaciones SPA/móviles

## ⚠️ MUY IMPORTANTE: Unicidad de Username

Esta advertencia aplica al metodo `getUsername()` de `AuthenticatedUserInterface`, NO al campo `username` de la entidad `User`.

En esta libreria, el valor devuelto por `AuthenticatedUserInterface::getUsername()` NO debe tratarse como identificador global unico del usuario.

La entidad `User` (autenticacion local) mantiene su propia regla de unicidad para `username` en base de datos.

- En escenarios JWT con multiples identity providers, el username puede repetirse entre emisores.
- En Keycloak, por ejemplo, `sub` suele ser un UUID interno estable y `preferred_username` es el login visible.
- Para identificar un principal de forma unica y estable, use `getId()`.

Recomendacion operativa:

- Use `getId()` para llaves de negocio, auditoria, correlacion de eventos y relaciones persistentes.
- Use `getUsername()` solo para presentacion, UX, logs funcionales o trazabilidad humana.

### OAuth 2.0 Client Credentials

```php
<?php
// Endpoint para obtener tokens JWT
// POST /oauth/token
$response = [
    'grant_type' => 'client_credentials',
    'client_id' => 'your_client_id',  
    'client_secret' => 'your_client_secret',
    'scope' => 'read write'
];
```

### Configuración Apache para JWT

Para que la autenticación JWT funcione correctamente con Apache, es necesario configurar el servidor para que pase el header `Authorization`:

```apache
# En VirtualHost o .htaccess
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1

# O usando RewriteRule
RewriteEngine On
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
```

## 📚 API Reference

### Interfaces Principales

#### AuthServiceInterface
```php
interface AuthServiceInterface
{
    public function login(string $username, string $password);
    public function logout(): void;
    public function getAuthenticatedUser(): ?AuthenticatedUserInterface;
}
```

#### AuthenticatedUserInterface
```php
interface AuthenticatedUserInterface
{
    // Información del usuario
    public function getId(): string;
    public function getUsername(): string;
    public function getFullName(): string;
    public function getEmail(): ?string;
    public function toArray(): array;
    
    // Roles
    public function hasRole(string $role): bool;
    public function hasAnyRole(array $roles): bool;
    public function hasAllRoles(array $roles): bool;
    
    // Permisos
    public function hasPermission(string $resource, string $permission, ?string $scope = null): bool;
    public function hasAnyPermission(array $resources, array $permission, ?array $scopes = null): bool;
    public function hasAllPermissions(array $resources, array $permission, ?array $scopes = null): bool;
}
```

#### JWTAuthenticatorInterface
```php
interface JWTAuthenticatorInterface
{
    public function authenticate(string $jwt): AuthenticationResult;
}
```

#### AuthenticationResult
```php
final class AuthenticationResult
{
    public function getAuthenticatedUser(): AuthenticatedUserInterface;
    public function getPayload(): array;
    public function getHeader(): array;
}
```

### GraphQL Schema

El módulo proporciona los siguientes tipos y queries GraphQL:

```graphql
type Query {
    login(username: String!, password: String!): AuthenticatedUser
    getSessionData: AuthenticatedUser
}

type AuthenticatedUser {
    fullName: String!
    firstName: String!
    lastName: String
    username: String!
    email: String
    picture: String
    roles: [String!]!
    permissions: [String!]!
}
```

### Enums y Tipos

```php
<?php
// GPDAuth\Enums\AuthenticatedUserType
enum AuthenticatedUserType: string
{
    case API_CLIENT = 'api_client';
    case LOCAL_USER = 'local_user';
    case EXTERN_USER = 'extern_user';
}

// GPDAuthJWT\Enums\ApiConsumerStatus
enum ApiConsumerStatus: string
{
    case ACTIVE = 'active';
    case REVOKED = 'revoked';
    case SUSPENDED = 'suspended';
}

enum PermissionAccess: string
{
    case ALLOW = 'allow';
    case DENY = 'deny';
}

enum PermissionValue: string
{
    case ALL = 'all';
    case VIEW = 'view';
    case CREATE = 'create';
    case UPDATE = 'update';
    case DELETE = 'delete';
    case UPLOAD = 'upload';
    case DOWNLOAD = 'download';
}

enum AuthenticationType: string
{
    case SESSION = 'session';
    case ACCESS_TOKEN = 'access_token';
    case REFRESH_TOKEN = 'refresh_token';
    case NONE = 'none';
}

enum HashAlgorithm: string
{
    case Argon2id = 'argon2id';
    case Bcrypt = 'bcrypt';
    case Sha256 = 'sha256';
    case Sha1 = 'sha1';
    case Md5 = 'md5';
}

enum JwtAlgorithm: string
{
    case HS256 = 'HS256';
    case HS384 = 'HS384';
    case HS512 = 'HS512';
    case RS256 = 'RS256';
    case RS384 = 'RS384';
    case RS512 = 'RS512';
    case ES256 = 'ES256';
    case ES384 = 'ES384';
    case ES256K = 'ES256K';
}
```

### Entidades de Base de Datos

La librería incluye las siguientes entidades Doctrine:

**GPDAuth (módulo base)**

- **User**: usuarios del sistema con autenticación local
- **Role**: roles asignables a usuarios
- **Resource**: recursos protegidos del sistema
- **Permission**: permisos específicos sobre recursos con soporte de scope

**GPDAuthJWT (módulo JWT)**

- **ApiConsumer**: clientes API para autenticación machine-to-machine (M2M)
- **ApiConsumerPermission**: permisos asignados a un cliente API sobre recursos concretos
- **ApiConsumerRoleMapping**: mapeo de roles externos del cliente API a roles internos del sistema
- **JWTKey**: claves criptográficas (pública/privada) para firma y verificación de JWTs
- **TrustedIssuer**: emisores JWT externos de confianza (p.ej. Keycloak, Auth0)
- **TrustedIssuerAudience**: audiencias (`aud`) válidas permitidas por cada emisor de confianza
- **TrustedIssuerRoleMapping**: mapeo de roles externos del emisor a roles internos del sistema

---

## 💡 Ejemplos Adicionales

### Personalización de Resolvers con Usuario

```php
<?php
$proxyEcho = fn($resolver) => function ($root, $args, AppContextInterface $context, $info) use ($resolver) {
    /** @var AuthServiceInterface */
    $authService = $context->getServiceManager()->get(AuthServiceInterface::class);
    $user = $authService->getAuthenticatedUser();
    
    if (!$user) {
        return $resolver($root, $args, $context, $info);
    }
    
    $msg = $resolver($root, $args, $context, $info);
    return sprintf("%s -> Usuario: %s", $msg, $user->getUsername());
};

return [
    'Query::echoWithUser' => ResolverPipelineFactory::createPipeline($echoResolve, [
        ResolverPipelineFactory::createWrapper($proxyEcho),
        AuthResolverGuardFactory::requireAuthenticated(),
    ]),
];
```

### Manejo de Errores

```php
<?php
use GPDAuth\Library\NoSignedException;
use GPDAuth\Library\NoAuthorizedException;

try {
    $user = static::getAuthenticatedUser($context);
    if (!$user) {
        throw new NoSignedException();
    }
    
    if (!$user->hasRole('admin')) {
        throw new NoAuthorizedException("Acceso denegado", "FORBIDDEN", 403);
    }
    
    // Lógica del resolver...
    
} catch (NoSignedException $e) {
    throw new GQLException('Debe iniciar sesión', 'UNAUTHENTICATED', 401);
} catch (NoAuthorizedException $e) {
    throw new GQLException('Permisos insuficientes', 'FORBIDDEN', 403);
}
```

### Uso sin GQLPDSS

Para usar la librería en proyectos sin GQLPDSS:

```bash
composer require wappcode/gql-pdss-auth
```

Agregue las entidades a la configuración de Doctrine:
```php
$entityPaths = [
    __DIR__ . "/../vendor/wappcode/gql-pdss-auth/GPDAuth/src/Entities"
];
```

Cree una instancia del servicio:
```php
<?php
use GPDAuth\Services\AuthSessionService;
use GPDAuth\Services\UserRepository;

$userRepository = new UserRepository($entityManager);
$authService = new AuthSessionService($userRepository);

// Uso del servicio
$authService->login('username', 'password');
$user = $authService->getAuthenticatedUser();
```

Este sistema de autenticación proporciona una base sólida y flexible para manejar la seguridad en aplicaciones PHP modernas con GraphQL y REST APIs.


## 📄 Licencia

Este proyecto está licenciado bajo la Licencia MIT - ver el archivo [LICENSE](LICENSE) para más detalles.
