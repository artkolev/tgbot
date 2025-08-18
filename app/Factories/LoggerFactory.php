<?php

declare(strict_types=1);

namespace TGBot\Factories;

use Monolog\Handler\StreamHandler;
use Monolog\Logger;

class LoggerFactory
{
    public static function build(): Logger
    {
        $logger = new Logger('systemLogger');
        $logger->pushHandler(new StreamHandler(
            BASE_PATH . '/logs/' . date('y_m_d') . '_errors' . '.log',
            Logger::ERROR
        ));
        $logger->pushHandler(new StreamHandler(
            BASE_PATH . '/logs/' . date('y_m_d') . '_debug' . '.log',
            Logger::DEBUG
        ));
        $logger->pushHandler(new StreamHandler('php://stderr'));

        return $logger;
    }
}
