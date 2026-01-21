<?php

use GPDAuth\Library\AuthConfigKey;
use GPDAuth\Library\AuthMethod;
use GPDAuth\Library\JwtAlgorithm;

return [
    AuthConfigKey::JwtSecureKey->value => getenv("GPDAUTH_CONFIG__JWT_SECURE_KEY") ? getenv("GPDAUTH_CONFIG__JWT_SECURE_KEY") : "12345",
    AuthConfigKey::AuthSessionKey->value => getenv("GPDAUTH_CONFIG__AUTH_SESSION_KEY") ? getenv("GPDAUTH_CONFIG__AUTH_SESSION_KEY") : "gpd_auth_session_key",
    AuthConfigKey::JwtExpirationTime->value => getenv("GPDAUTH_CONFIG__JWT_EXPIRATION_TIME_KEY") ? (int)getenv("GPDAUTH_CONFIG__JWT_EXPIRATION_TIME_KEY") : 1200, // Tiempo en segundos
    AuthConfigKey::JwtAlgorithm->value => getenv("GPDAUTH_CONFIG__JWT_ALGORITHM_KEY") ? JwtAlgorithm::tryFromString(getenv("GPDAUTH_CONFIG__JWT_ALGORITHM_KEY"), JwtAlgorithm::HS256)->value : JwtAlgorithm::HS256->value,
    AuthConfigKey::AuthMethodKey->value => getenv("GPDAUTH_CONFIG__AUTH_METHOD_KEY") ? AuthMethod::tryFromString(getenv("GPDAUTH_CONFIG__AUTH_METHOD_KEY")) ?? AuthMethod::SessionOrJwt : AuthMethod::SessionOrJwt,
    AuthConfigKey::AuthIssKey->value => getenv("GPDAUTH_CONFIG__AUTH_ISS_KEY") ? getenv("GPDAUTH_CONFIG__AUTH_ISS_KEY") : ($_SERVER["SERVER_NAME"] ?? "localhost"),
    AuthConfigKey::JwtIssConfig->value => [
        // se agregan los datos de los iss usando el nombre como clave
        "https://issurl" => [  //  url o id del iss
            AuthConfigKey::JwtSecureKey->value => "secure_key_to_iss", // opcional si no esta definido utiliza la clave de la configuración local
            AuthConfigKey::JwtAlgorithm->value => JwtAlgorithm::HS256->value, // opcional si no esta definido utiliza el algoritmo de la configuración local
            AuthConfigKey::AuthIssAllowedRoles->value => [ // array con los roles permitidos para el iss  se permite el mapeo de un rol del iss a uno del sistema
                "iss_admin_role" => "admin",
                "custom_role" => "custom_role"
            ]
        ]
    ]

];
