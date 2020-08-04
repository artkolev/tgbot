<?php

namespace TGBot;

use League\Container\Container;
use League\Container\ReflectionContainer;
use League\Container\ServiceProvider\AbstractServiceProvider;
use League\Container\ServiceProvider\BootableServiceProviderInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\HttpFoundation\Session\Storage\NativeSessionStorage;
use TGBot\Factories\CacheFactory;
use TGBot\Factories\DoctrineFactory;
use TGBot\Factories\LoggerFactory;
use TGBot\Factories\TelegramFactory;
use TGBot\Repositories\SecretSantaEventMembersRepository;
use TGBot\Repositories\SecretSantaEventsRepository;
use TGBot\Repositories\UserChatRepository;

class ServiceProvider extends AbstractServiceProvider implements BootableServiceProviderInterface
{

    protected $provides = [
        'logger',
        'entityManager',
        'telegram',
        'commandsPath',

        'SecretSantaEventMembersRepository',
        'SecretSantaEventsRepository',
        'UserChatRepository'
    ];

    public function register(): void
    {

        $this->getContainer()->share('logger', LoggerFactory::build());

        $this->getContainer()
            ->share(Request::class, Request::createFromGlobals());

        $this->getContainer()
            ->share(Session::class)
            ->withArgument(new NativeSessionStorage());

        $this->getContainer()
            ->share('cache', CacheFactory::build());

        $this->getContainer()
            ->share('entityManager', DoctrineFactory::build());

        $this->getContainer()
            ->share('telegram', TelegramFactory::build());

        $this->getContainer()
            ->add('commandsPath', realpath(__DIR__ . '/../commands/'));

        $this->getContainer()
            ->share(SecretSantaEventMembersRepository::class);

        $this->getContainer()
            ->share(SecretSantaEventsRepository::class);

        $this->getContainer()
            ->share(UserChatRepository::class);
    }

    /**
     * Method will be invoked on registration of a service provider implementing
     * this interface. Provides ability for eager loading of Service Providers.
     *
     * @return void
     */
    public function boot()
    {
        /** @var Container $container */
        $container = $this->getContainer();
        $container->delegate(new ReflectionContainer());
    }
}
