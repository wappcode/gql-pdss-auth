<?php

use GQLBasicClient\GQLClient;
use GPDCore\Factory\EntityManagerFactory;

require_once __DIR__ . "/../vendor/autoload.php";
$options = require __DIR__ . "/../dev/config/doctrine.local.php";
$cacheDir = __DIR__ . "/../dev/data/DoctrineORMModule";
global $entityManager;
global $graphqlClient;
$entityManager = EntityManagerFactory::createInstance($options, $cacheDir, true, '');
$app_port = getenv("GQLPDSSAUTH_APP_PORT") ? getenv("GQLPDSSAUTH_APP_PORT") : "8080";
$graphqlApi = "http://localhost:{$app_port}/index.php/api";
$graphqlClient = new GQLClient($graphqlApi);
