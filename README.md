# GPDAuth wappcode/gql-pdss-auth

Libreria para agregar autentificación a un proyecto php.

Compatible con la librería wappcode/gqlpdss

## Instalar con GQLPDSS

En un proyecto wappcode/gqlpdss ejecutar el siguiente comando

```
composer require wappcode/gql-pdss-auth
```

Agregar las entidades doctrine

```
    // archivo config/doctrine.entities.php

    <?php

    return  [
        // ...
        "GPDAuth\Entities" => __DIR__ . "/../vendor/wappcode/gql-pdss-auth/GPDAuth/src/Entities"
        // ...
    ];

```

Ejecutar comando para actualizar la base de datos

```
    vendor/bin/doctrine orm:schema-tool:update --force
```

Establecer configuración por archivo

```
// config/local.config.php

<?php

use GPDAuth\Library\AuthConfigKey;
use GPDAuth\Library\AuthMethod;
use GPDAuth\Library\JwtAlgorithm;

return [
    // configuración de otros módulos
    // .....
    AuthConfigKey::JwtSecureKey->value => "secret_key",
    AuthConfigKey::AuthSessionKey->value => "gpd_auth_session_key", // key o indice asociativo del array $_SESSION para authentificación
    AuthConfigKey::JwtExpirationTime->value => 1200, // Tiempo en segundos
    AuthConfigKey::JwtAlgorithm->value => JwtAlgorithm::HS256->value,
    AuthConfigKey::AuthMethodKey->value => AuthMethod::SessionOrJwt,
    AuthConfigKey::AuthIssKey->value => $_SERVER["SERVER_NAME"] ?? "localhost",
    AuthConfigKey::JwtIssConfig->value => [
        // se agregan los datos de los iss usando el nombre como clave
        "https://issurl" => [  //  url o id del iss
            AuthConfigKey::JwtSecureKey->value => "secure_key_to_iss",// opcional si no esta definido utiliza la clave de la configuración local
            AuthConfigKey::JwtAlgorithm->value => JwtAlgorithm::HS256->value, // opcional si no esta definido utiliza el algoritmo de la configuración local
            AuthConfigKey::AuthIssAllowedRoles->value => [ // array con los roles permitidos para el iss  se permite el mapeo de un rol del iss a uno del sistema
                "iss_admin_role" => "admin",
                "custom_role" => "custom_role"
            ]
        ]
    ]
    // .....
];
```

Establecer configuración por variable de entorno (alternativa)

Variables:

```
GPDAUTH_CONFIG__JWT_SECURE_KEY
GPDAUTH_CONFIG__GPDAUTH_CONFIG__AUTH_SESSION_KEY
GPDAUTH_CONFIG__GJWT_EXPIRATION_TIME_KEY
GPDAUTH_CONFIG__JWT_ALGORITHM_KEY
GPDAUTH_CONFIG__AUTH_METHOD_KEY
GPDAUTH_CONFIG__AUTH_ISS_KEY
```

Agregar el módulo

```
// public/index.php

<?php
//...
use GPDAuth\GPDAuthModule;
//...
$app->addModules([
    GPDAuthModule::class, // se agrega módulo de autentificación
    // Otros módulos
    //...
    AppModule::class,
]);
//...

```

Para agregar seguridad a un resolver o ruta utilizar el servicio AuthService

```
<?php
//...

$auth = $this->context->getServiceManager()->get(AuthService::class);
// Revisa si el usuario esta firmado
$auth->isSigned();

```

## Métodos AuthService

El servicio AuthService cuenta con métodos utiles para determinar si un usuario tiene authorización a un recurso

### isSigned

Retorna true si el usuario esta firmado

### login

Hace el login de un usuario

```
$auth->login("username","passwoerd");
```

### logout

Hace el logout de un usuario

Si se utiliza JWT se limpia la sesión pero el JWT sigue siendo válido hasta que expira

```
$auth->login("username","passwoerd");
```

### hasRole

Retorna true si el usuario tiene un determinado rol

```
// Revisa si el usuario tiene el rol de admin
$auth->hasRole("admin");
```

### hasSomeRoles

Retorna true si el usuario tiene alguno de los roles

```
// Revisa si el usuario tiene alguno de los roles (staff, publisher)

$auth->hasSomeRoles(["staff", "publisher"]);
```

### hasAllRoles

Retorna true si el usuario tiene asignados todos los roles

```
// Revisa si el usuario tiene todos los roles (staff, publisher)

$auth->hasAllRoles(["staff", "publisher"]);
```

### hasPermission

Retorna true si el usuario tiene el permiso

Los permisos pueden ser específicos por usuario por rol o globales. La prioridad se aplica en ese orden (permisos usuario, permisos rol, permisos globales).

El scope se puede utilizar para identificar si un usuario tiene permisos para un recurso pero con restricciones por ejemplo que tenga permisos para el recurso POST pero solo pueda editar los que le pertenecen a él

```
$auth->hasPermission("resource_post","VIEW"); // retorna true si el usuario tiene permiso para ver post sin importar el scope

$auth->hasPermission("resource_post","VIEW","OWNER"); // retorna true si el usuario tiene permiso para ver post pero con scope OWNER

$auth->hasPermission("resource_post","VIEW","ALL"); // retorna true si el usuario tiene permiso para ver post pero con scope ALL
```

### hasSomePermissions

Retorna true si el usuario tiene uno o más de los permisos.

Se pueden pasar multiples recursos, permisos y scopes, se realizan la combinación de todos para determinar si tiene alguno

```
$auth->hasSomePermissions(["resource_post"],["CREATE","UPDATE"],["ALL"]);

```

### hasAllPermissions

Retorna true si el usuario tiene todos los permisos.

Se pueden pasar multiples recursos, permisos y scopes, se realizan las combinaciones para determinar si los tiene todos

```
$auth->hasSomePermissions(["resource_post"],["VIEW",CREATE","UPDATE","DELETE"],["ALL"]);

```

## USAR SIN GQLPDSS

Intalar

```
composer require wappcode/gql-pdss-auth
```

Agregar a las rutas de doctrine las entidades del módulo

```
__DIR__ . "/../vendor/wappcode/gql-pdss-auth/GPDAuth/src/Entities"
```

Actualizar base de datos

```
vendor/bin/doctrine orm:schema-tool:update --force
```

## Configuración de Apache

Para que la autenticación JWT funcione correctamente con Apache, es necesario configurar el servidor para que pase el header `Authorization` a PHP. Por defecto, Apache no transfiere este header por razones de seguridad.

### Opción 1: Usando SetEnvIf (Recomendado)

Agregar en el archivo de configuración de VirtualHost o en `.htaccess`:

```apache
SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
```

### Opción 2: Usando RewriteRule

Alternativamente, si ya tienes `RewriteEngine On` configurado:

```apache
RewriteEngine On
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
```

### Ejemplo completo en VirtualHost

```apache
<VirtualHost *:80>
    ServerName example.com
    DocumentRoot /var/www/html/public
    
    # Pasar header Authorization a PHP
    SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
    
    # Otras configuraciones...
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ /index.php?path=$1 [NC,L,QSA]
</VirtualHost>
```

**Nota:** Después de modificar la configuración de Apache, recuerda reiniciar el servicio:
```bash
sudo service apache2 restart
# o en Docker
docker-compose restart
```

Crear una instancia de la clase AuthService y utilizar sus métodos para login, revisar roles y revisar permisos

```
<?php

//...
$auth = new AuthService(
                        $entityManager,
                        $_SERVER["SERVER_NAME"] ?? "localhost",
                        "JWT", // o SESSION
                    );

$auth->setJwtAlgoritm("HS256");
$auth->setjwtExpirationTimeInSeconds(1200);// En segundos
$auth->setJwtSecureKey("secret_key");
$auth->initSession();

//...

```
