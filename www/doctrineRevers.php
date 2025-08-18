<?php

declare(strict_types=1);

use Dotenv\Dotenv;
use TGBot\Factories\ServiceProviderFactory;

define('BASE_PATH', dirname(__DIR__, 1));

include __DIR__ . '/../vendor/autoload.php';

$dotEnv = Dotenv::create(BASE_PATH);
$dotEnv->overload();
$dotEnv->required([
    'MYSQL_HOST',
    'MYSQL_PORT',
    'MYSQL_USER',
    'MYSQL_PASS',
    'MYSQL_DB',
    'BOT_API_KEY',
    'BOT_USERNAME',
    'BOT_HOOK_URL'
]);

$di = ServiceProviderFactory::build();

$em = $di->get('entityManager');

$cme = new \Doctrine\ORM\Tools\Export\ClassMetadataExporter();

$exporter = $cme->getExporter('yml', './yml');

$classes = array(
    $em->getClassMetadata('TGBot\Entities\User'),
    $em->getClassMetadata('TGBot\Entities\UserChat')
);
$exporter->setMetadata($classes);
$exporter->export();
