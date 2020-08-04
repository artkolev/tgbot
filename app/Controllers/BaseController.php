<?php

namespace TGBot\Controllers;

use League\Container\Container;
use Longman\TelegramBot\Telegram;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;
use Symfony\Component\HttpFoundation\Request;
use TGBot\Factories\ServiceProviderFactory;

abstract class BaseController implements LoggerAwareInterface
{

    use LoggerAwareTrait;

    /**
     * @var Container
     */
    protected $DI;

    /**
     * @var Request
     */
    protected $request;

    /**
     * @var Telegram
     */
    protected $telegram;

    /**
     * BaseController constructor.
     */
    public function __construct()
    {
        $this->DI = ServiceProviderFactory::build();
        $this->request = $this->DI->get(Request::class);
        $this->logger = $this->DI->get('logger');
        $this->telegram = $this->DI->get('telegram');
    }

    /**
     * Получение json соджержимого
     * @return array
     */
    protected function getContentAsArray(): array
    {
        $content = $this->request->getContent();

        return json_decode($content, true);
    }

    /**
     * Получение переданных параметров
     * @return array
     */
    protected function getParametersFromRequest(): array
    {
        $data = [];
        parse_str($this->request->getContent(), $data);

        if (!count($data)) {
            $data = $this->request->request->all();
        }

        return $data;
    }
}
