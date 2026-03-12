<?php

use GPDAuth\Library\JwtAlgorithm;

return [
    'gpd_auth_jwt_secure_key' => "vPW3NTI&0+",
    'gpd_auth_session_key' => "gpd_auth_session_key", // key o indice asociativo del array $_SESSION para authentificación
    'gpd_auth_jwt_default_expiration_time' => 1200, // Tiempo en segundos
    'gpd_auth_jwt_algorithm_key' => JwtAlgorithm::HS256->value,
    'gpd_auth_auth_method_key' => 'SESSION_OR_JWT',
    'gpd_auth_iss_key' => $_SERVER["SERVER_NAME"] ?? "localhost",
];
