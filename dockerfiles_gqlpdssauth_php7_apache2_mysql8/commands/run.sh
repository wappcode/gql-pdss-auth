#!/bin/bash
composer install --no-interaction
vendor/bin/doctrine orm:schema-tool:update --force
apache2-foreground
