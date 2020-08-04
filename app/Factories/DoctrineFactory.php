<?php

namespace TGBot\Factories;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Setup;

class DoctrineFactory
{
    public static function build(): EntityManager
    {
        // Create a simple "default" Doctrine ORM configuration for Annotations
        $isDevMode = getenv('DEV');
        $proxyDir = null;
        $cache = null;
        $useSimpleAnnotationReader = false;
        $config = Setup::createAnnotationMetadataConfiguration(
            [__DIR__ . '/../'],
            $isDevMode,
            $proxyDir,
            $cache,
            $useSimpleAnnotationReader
        );

        // database configuration parameters
        $conn = [
            'driver' => 'pdo_mysql',
            'host' => getenv('MYSQL_HOST'),
            'port' => getenv('MYSQL_PORT'),
            'user' => getenv('MYSQL_USER'),
            'password' => getenv('MYSQL_PASS'),
            'dbname' => getenv('MYSQL_DB')
        ];

        $entityManager = EntityManager::create($conn, $config);
        return $entityManager;
    }
}
