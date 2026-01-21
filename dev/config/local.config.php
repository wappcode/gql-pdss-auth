<?php

use GPDAuth\Library\AuthConfigKey;
use GPDAuth\Library\AuthMethod;
use GPDAuth\Library\JwtAlgorithm;

return [
    AuthConfigKey::JwtSecureKey->value => "vPW3NTI&0+",
    AuthConfigKey::AuthSessionKey->value => "gpd_auth_session_key", // key o indice asociativo del array $_SESSION para authentificación
    AuthConfigKey::JwtExpirationTime->value => 1200, // Tiempo en segundos
    AuthConfigKey::JwtAlgorithm->value => JwtAlgorithm::HS256->value,
    AuthConfigKey::AuthMethodKey->value => AuthMethod::SessionOrJwt,
    AuthConfigKey::AuthIssKey->value => $_SERVER["SERVER_NAME"] ?? "localhost",
];
