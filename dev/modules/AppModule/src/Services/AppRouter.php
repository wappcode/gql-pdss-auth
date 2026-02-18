<?php

namespace AppModule\Services;

use GPDCore\Controllers\GraphqlController;
use GPDCore\Routing\AbstractRouter;
use GPDCore\Routing\RouteModel;

class AppRouter extends AbstractRouter
{


    protected function addRoutes()
    {
        $GraphqlMethod = $this->isProductionMode ? 'POST' : ['POST', 'GET'];

        // Agrega las entradas para consultas graphql
        $this->addRoute(new RouteModel($GraphqlMethod, '/api', GraphqlController::class));

        // Las demás rutas deben ir abajo para poder utilizar la configuración de los módulos y sus servicios

        // entrada dominio principal

        // ... otras rutas
    }
}
