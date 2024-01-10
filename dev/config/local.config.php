<?php

use GPDAuth\Library\AuthConfig;
use GPDAuth\Library\IAuthService;

return [
    AuthConfig::JWT_SECURE_KEY => "vPW3NTI&0+",
    AuthConfig::AUTH_SESSION_KEY => "gpd_auth_session_key", // key o indice asociativo del array $_SESSION para authentificación
    AuthConfig::JWT_EXPIRATION_TIME_KEY => 1200, // Tiempo en segundos
    AuthConfig::JWT_ALGORITHM_KEY => 'HS256',
    AuthConfig::AUTH_METHOD_KEY => IAuthService::AUTHENTICATION_METHOD_SESSION_OR_JWT,
    AuthConfig::AUTH_ISS_KEY => $_SERVER["SERVER_NAME"] ?? "localhost",
];
