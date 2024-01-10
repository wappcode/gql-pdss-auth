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
];
