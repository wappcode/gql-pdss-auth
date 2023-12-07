<?php
ini_set("display_error", 1);
error_reporting(E_ALL);
echo "\n Preparando para insertar datos en la  base de datos \n";
$user = getenv("GQLPDSSAUTH_DBUSER") ? getenv("GQLPDSSAUTH_DBUSER") : 'root';
$pass = getenv("GQLPDSSAUTH_DBPASSWORD") ?  getenv("GQLPDSSAUTH_DBPASSWORD") : 'dbpassword';
$host = "gqlpdssauth-mysql";
$databasename = getenv("GQLPDSSAUTH_DBNAME") ?  getenv("GQLPDSSAUTH_DBNAME") : 'gqlpdss_authdb';
$pdo = new PDO("mysql:host={$host};dbname={$databasename}", $user, $pass);

$sql = file_get_contents(__DIR__ . "/gqlpdss_authdb.sql");
echo "\n Insertando datos {$databasename};\n";
echo $sql;
try {
    $pdo->query($sql);
    echo "\n Datos insertados\n";
} catch (Exception $e) {
    echo $e->getMessage();
}
