<?php

use AppModule\AppModule;
use AppModule\Services\AppRouter;
use GPDCore\Library\GPDApp;
use GPDCore\Services\ContextService;
use Laminas\ServiceManager\ServiceManager;

require_once __DIR__ . "/../../vendor/autoload.php";
$configFile = __DIR__ . "/../config/doctrine.local.php";
$cacheDir = __DIR__ . "/../data/DoctrineORMModule";
$enviroment = getenv("APP_ENV");
$serviceManager = new ServiceManager();
$context = new ContextService($serviceManager);
$context->setDoctrineConfigFile($configFile);
$context->setDoctrineCacheDir($cacheDir);
$router = new AppRouter();
$app = new GPDApp($context, $router, $enviroment);
$app->addModules([
    AppModule::class,
]);
$localConfig = require __DIR__ . "/../config/local.config.php";
$context->getConfig()->add($localConfig);
$app->run();
