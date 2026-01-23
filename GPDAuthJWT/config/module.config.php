<?php


return [
    "idp_jwt" => [
        "lifetime_seconds" => 3600,
        "issuer" => getenv("JWT_ISSUER") ? getenv("JWT_ISSUER") : "https://auth.example.com",
        "audience" => getenv("JWT_AUDIENCE") ? getenv("JWT_AUDIENCE") : "https://api.example.com"
    ]
];
