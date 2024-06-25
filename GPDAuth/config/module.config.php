<?php

use GPDAuth\Library\AuthConfig;
use GPDAuth\Library\IAuthService;

return [
    AuthConfig::JWT_SECURE_KEY => getenv("GPDAUTH_CONFIG__JWT_SECURE_KEY") ? getenv("GPDAUTH_CONFIG__JWT_SECURE_KEY") : "12345",
    AuthConfig::JWT_SECURE_KEY => getenv("GPDAUTH_CONFIG__AUTH_SESSION_KEY") ? getenv("GPDAUTH_CONFIG__AUTH_SESSION_KEY") : "gpd_auth_session_key",
    AuthConfig::JWT_EXPIRATION_TIME_KEY => getenv("GPDAUTH_CONFIG__JWT_EXPIRATION_TIME_KEY") ? getenv("GPDAUTH_CONFIG__JWT_EXPIRATION_TIME_KEY") : 1200, // Tiempo en segundos
    AuthConfig::JWT_ALGORITHM_KEY => getenv("GPDAUTH_CONFIG__JWT_ALGORITHM_KEY") ? getenv("GPDAUTH_CONFIG__JWT_ALGORITHM_KEY") : "HS256", // Tiempo en segundos
    AuthConfig::AUTH_METHOD_KEY => getenv("GPDAUTH_CONFIG__AUTH_METHOD_KEY") ? getenv("GPDAUTH_CONFIG__AUTH_METHOD_KEY") : IAuthService::AUTHENTICATION_METHOD_SESSION_OR_JWT, // Tiempo en segundos
    AuthConfig::AUTH_ISS_KEY => getenv("GPDAUTH_CONFIG__AUTH_ISS_KEY") ? getenv("GPDAUTH_CONFIG__AUTH_ISS_KEY") : ($_SERVER["SERVER_NAME"] ?? "localhost"), // Tiempo en segundos
    AuthConfig::JWT_ISS_CONFIG => [
        // se agregan los datos de los iss usando el nombre como clave
        "https://issurl" => [  //  url o id del iss
            AuthConfig::JWT_SECURE_KEY => "secure_key_to_iss", // opcional si no esta definido utiliza la clave de la configuración local
            AuthConfig::JWT_ALGORITHM_KEY => "HS256", // opcional si no esta definido utiliza el algoritmo de la configuración local
            AuthConfig::AUTH_ISS_ALLOWED_ROLES => [ // array con los roles permitidos para el iss  se permite el mapeo de un rol del iss a uno del sistema
                "iss_admin_role" => "admin",
                "custom_role" => "custom_role"
            ]
        ]
    ]

];
