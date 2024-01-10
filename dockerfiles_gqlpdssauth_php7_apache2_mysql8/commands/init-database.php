<?php
echo "\n Preparando para inicializar base de datos \n";
$user = getenv("GQLPDSSAUTH_DBUSER") ? getenv("GQLPDSSAUTH_DBUSER") : 'root';
$pass = getenv("GQLPDSSAUTH_DBPASSWORD") ?  getenv("GQLPDSSAUTH_DBPASSWORD") : 'dbpassword';
$host = getenv("GQLPDSSAUTH_DBHOST") ?  getenv("GQLPDSSAUTH_DBHOST") : 'localhost';
$databasename = getenv("GQLPDSSAUTH_DBNAME") ?  getenv("GQLPDSSAUTH_DBNAME") : 'gqlpdss_authdb';
$pdo = new PDO("mysql:host={$host}", $user, $pass);
echo "\n Limpiando base de datos {$databasename} \n";
$pdo->exec("DROP DATABASE IF EXISTS {$databasename};");
echo "\n Creando base de datos {$databasename};";
$pdo->exec("CREATE DATABASE IF NOT EXISTS {$databasename};");
