<?php
// Paths to Entities that we want Doctrine to see
$paths = array(
    "app/Models"
);

// Tells Doctrine what mode we want
$isDevMode = true;

// Doctrine connection configuration
$dbParams = array(
    'driver' => 'pdo_mysql',
    'host' => getenv('MYSQL_HOST'),
    'port' => getenv('MYSQL_PORT'),
    'user' => getenv('MYSQL_USER'),
    'password' => getenv('MYSQL_PASS'),
    'dbname' => getenv('MYSQL_DB')
);
