<?php

declare(strict_types=1);

namespace TGBot\Commands\AdminCommands;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityManager;
use League\Container\Container;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Telegram;
use Monolog\Logger;
use TGBot\Factories\ServiceProviderFactory;
use TGBot\Traits\CommandsTrait;

abstract class AdminBaseClass extends UserCommand
{
    use CommandsTrait;

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

    public function __construct(Telegram $telegram, Update $update = null)
    {
        $this->di = ServiceProviderFactory::build();
        $this->logger = $this->di->get('logger');
        $this->entityManager = $this->di->get('entityManager');
        $this->pdo = $this->entityManager->getConnection()->getNativeConnection();

        parent::__construct($telegram, $update);
    }
}
