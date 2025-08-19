<?php

declare(strict_types=1);

namespace TGBot\Base;

use FastRoute\Dispatcher;
use FastRoute\RouteCollector;
use TGBot\Controllers\IndexController;
use TGBot\Controllers\WebHookController;
use function FastRoute\simpleDispatcher;

class Route
{

    protected static $routers = [
        ['GET', '/', [IndexController::class, 'get']],
        ['POST', '/webhook/{token}/', [WebHookController::class, 'hook']],
    ];

    public static function init(): Dispatcher
    {
        $dispatcher = simpleDispatcher(static function (RouteCollector $r) {
            foreach (self::$routers as $route) {
                $r->addRoute($route[0], $route[1], $route[2]);
            }
        });

        return $dispatcher;
    }
}
