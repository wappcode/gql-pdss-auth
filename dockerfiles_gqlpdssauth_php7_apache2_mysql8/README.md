## Iniciar servidor php7.4 y mysql8 Docker

Para iniciar el servidor usar el comando

    docker compose up

Se puede pasar variables de entorno como el siguiente ejemplo

    docker compose build && docker compose run  -e GQLPDSSAUTH_APP_PORT=8080 -e GQLPDSSAUTH_MYSQL_PORT=3308 -e DB_PASSWORD=dbpassword gqlpdsslib-mysql gqlpdsslib-php
    GQLPDSSAUTH_APP_PORT=8080;GQLPDSSAUTH_MYSQL_PORT=3308;DB_PASSWORD=dbpassword docker compose up

Si se esta usando un archivo Dockerfile para compilar la imagen yhay cambios usar el siguiente comando para compilarlas

    docker-compose build

## Crear base de datos

- Iniciar servicios
- Ingresar a la linea de comandos mysql y dar de alta la base de datos

  mysql --port 3308 -h 127.0.0.1 -uroot -pdbpassword procesot_survey < ~/archivosbd.sql

## Iniciar librerias de php

Ingresar a bash del servicio php

    docker exec -it gqlpdssauth-php7.4 bash

## Xdebug

Para VSCode agregar la siguiente configuración en launch.json

     {
            "name": "Docker Listen for Xdebug",
            "type": "php",
            "request": "launch",
            "port": 9003,
            "pathMappings": {
                "/var/www/html": "${workspaceFolder}"
            },
            "hostname": "localhost" // se agrega solo para wsl windows  para que funcione
    },
