<?php

namespace GPDAuth\Library;

class AuthConfig
{

    const JWT_ALGORITHM_KEY = "gpd_auth_jwt_algorithm_key";
    const JWT_SECURE_KEY = "gpd_auth_jwt_secure_key";
    const AUTH_SESSION_KEY = "gpd_auth_session_key";
    const AUTH_METHOD_KEY = "gpd_auth_auth_method_key";
    const AUTH_ISS_KEY = 'gpd_auth_iss_key';
    const JWT_EXPIRATION_TIME_KEY = 'gpd_auth_jwt_default_expiration_time';
    const JWT_DEFAULT_EXPIRATION_TIME = 'gpd_auth_jwt_default_expiration_time';
}
