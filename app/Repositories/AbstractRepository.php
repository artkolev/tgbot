<?php

namespace TGBot\Repositories;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityManager;
use League\Container\Container;
use Monolog\Logger;
use TGBot\Factories\ServiceProviderFactory;

class AbstractRepository
{
    /**
     * @var Container
     */
    protected $di;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var Connection
     */
    protected $pdo;
    /**
     * @var Logger
     */
    protected $logger;

    public function __construct()
    {
        $this->di = ServiceProviderFactory::build();
        $this->logger = $this->di->get('logger');
        $this->entityManager = $this->di->get('entityManager');
        $this->pdo = $this->entityManager->getConnection()->getWrappedConnection();
    }
}