<?php
return [
    "driver" => [
        'user'     =>   getenv('GQLPDSSAUTH_DBUSER') ? getenv('GQLPDSSAUTH_DBUSER') : 'root',
        'password' =>   getenv('GQLPDSSAUTH_DBPASSWORD') ? getenv('GQLPDSSAUTH_DBPASSWORD') : 'dbpassword',
        'dbname'   =>   getenv('GQLPDSSAUTH_DBNAME') ? getenv('GQLPDSSAUTH_DBNAME') : 'gqlpdss_authdb',
        'driver'   =>   'pdo_mysql',
        'host'   =>     getenv('GQLPDSSAUTH_DBHOST') ? getenv('GQLPDSSAUTH_DBHOST') : 'localhost',
        'charset' =>    'utf8mb4'
    ],
    "entities" => require __DIR__ . "/doctrine.entities.php"
];
