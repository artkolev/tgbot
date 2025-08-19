<?php

declare(strict_types=1);

namespace TGBot\Factories;

use League\Container\Container;
use League\Container\ReflectionContainer;
use TGBot\ServiceProvider;

/**
 * Вспомогательный класс для ServiceProvider
 */
class ServiceProviderFactory
{
    /**
     * возврщает контейнер с автоинекцией
     * @return Container
     */
    public static function build(): Container
    {
        static $container;

        if (!$container instanceof Container) {
            $container = new Container();
            $container->addServiceProvider(ServiceProvider::class);
            $container->delegate(new ReflectionContainer());
        }

        return $container;
    }
}
