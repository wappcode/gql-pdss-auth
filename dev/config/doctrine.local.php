<?php
return [
    "driver" => [
        'user'     =>   'root',
        'password' =>   'dbpassword',
        'dbname'   =>   'gqlpdss_auth',
        'driver'   =>   'pdo_mysql',
        'host'   =>     '127.0.0.1',
        'charset' =>    'utf8mb4'
    ],
    "entities" => require __DIR__ . "/doctrine.entities.php"
];
