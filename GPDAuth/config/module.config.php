<?php

use GPDAuth\Library\AuthConfig;

return [
    AuthConfig::JWT_SECURE_KEY => "",
    AuthConfig::AUTH_SESSION_KEY => "gpd_auth_session_key", // key o indice asociativo del array $_SESSION para authentificación
    AuthConfig::JWT_EXPIRATION_TIME_KEY => 1200,
    AuthConfig::JWT_ALGORITHM_KEY => 'HS256',
];
