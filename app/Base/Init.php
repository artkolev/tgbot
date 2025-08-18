<?php

declare(strict_types=1);

namespace TGBot\Base;

use Doctrine\ORM\EntityManager;
use Dotenv\Dotenv;
use Dotenv\Exception\InvalidPathException;
use Dotenv\Exception\ValidationException;
use Exception;
use FastRoute\Dispatcher;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Telegram;
use Longman\TelegramBot\TelegramLog;
use RuntimeException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use TGBot\Factories\ServiceProviderFactory;

class Init
{

    public static function start(): bool
    {

        ini_set('memory_limit', '2048M');
        define('BASE_PATH', dirname(__DIR__, 2));
        error_reporting(-1);
        error_reporting(E_ALL & ~E_USER_DEPRECATED & ~E_DEPRECATED);
        ini_set('display_errors', 1);

        try {
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
        } catch (InvalidPathException $e) {
            error_log($e->getMessage());
            $response = JsonResponse::create('application error', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            $response->send();

            return false;
        } catch (ValidationException $e) {
            error_log($e->getMessage());
            $response = JsonResponse::create('application error', JsonResponse::HTTP_INTERNAL_SERVER_ERROR);
            $response->send();

            return false;
        }

        $di = ServiceProviderFactory::build();

        /** @var EntityManager $entityManager */
        $entityManager = $di->get('entityManager');

        try {
            TelegramLog::initialize($di->get('logger'));
            /** @var Telegram $telegram */
            $telegram = $di->get('telegram');
            $telegram->addCommandsPath($di->get('commandsPath'));
            TelegramLog::debug('Found commands', $telegram->getCommandsList());
            /** @noinspection PhpParamsInspection */
            $telegram->enableExternalMySql($entityManager->getConnection()->getNativeConnection());
        } catch (TelegramException $e) {
            TelegramLog::error($e->getMessage());

            if (getenv('DEV')) {
                throw new RuntimeException($e);
            }
            return false;
        }

        $dispatcher = Route::init();

        $request = Request::createFromGlobals();

        $route_info = $dispatcher->dispatch($request->getMethod(), $request->getPathInfo());

        switch ($route_info[0]) {
            case Dispatcher::NOT_FOUND:
                $response = Response::create('404 Not Found', Response::HTTP_NOT_FOUND);
                break;
            case Dispatcher::METHOD_NOT_ALLOWED:
                $response = Response::create('405 Method Not Allowed', Response::HTTP_METHOD_NOT_ALLOWED);
                break;
            case Dispatcher::FOUND:
                [$class_name, $method] = $route_info[1];
                $vars = $route_info[2];

                try {
                    $object = $di->get($class_name);
                    $response = call_user_func_array([$object, $method], $vars);
                } catch (Exception $ex) {
                    $di->get('logger')->error($ex->getMessage());
                    $response = JsonResponse::create(
                        ['error' => 'application error'],
                        JsonResponse::HTTP_INTERNAL_SERVER_ERROR
                    );
                }

                break;
            default:
                $response = Response::create('404 Not Found', Response::HTTP_NOT_FOUND)->send();
        }

        if ($response instanceof Response) {
            $response->prepare(Request::createFromGlobals());
            $response->send();
        }

        return true;
    }
}
