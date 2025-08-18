<?php

declare(strict_types=1);

namespace TGBot\Commands\UserCommands;

use Cache\Bridge\SimpleCache\SimpleCacheBridge;
use Exception;
use GuzzleHttp\Client;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\TelegramLog;
use Psr\SimpleCache\InvalidArgumentException;
use TGBot\Factories\ServiceProviderFactory;

class PadonakCommand extends UserBaseClass
{

    /**
     * @var string
     */
    protected $name = 'padonak';

    /**
     * @var string
     */
    protected $description = 'Перевод на йАзЫг пАдОнКаФф';

    /**
     * @var string
     */
    protected $usage = '/padonak <текст>';

    /**
     * Show in Help
     *
     * @var bool
     */
    protected $show_in_help = true;

    /**
     * Version
     *
     * @var string
     */
    protected $version = '1.0.0';

    /**
     * If this command is enabled
     *
     * @var boolean
     */
    protected $enabled = true;

    /**
     * Ответить отправителю команды
     *
     * @var bool
     */
    protected $replyToSender = true;

    /**
     * @inheritdoc
     */
    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chatId = $message->getChat()->getId();
        $text = mb_strtolower(trim($message->getText(true)));

        $data = [
            'chat_id' => $chatId,
            'parse_mode' => 'markdown',
        ];

        if (empty($text) && empty($message->getReplyToMessage())) {
            $data['text'] = 'Отсутствует текст запроса';
            return $this->sendAnswerRequest($data);
        }

        if (empty($text)) {
            $text = mb_strtolower(trim($message->getReplyToMessage()->getText(true)));
        }

        $di = ServiceProviderFactory::build();
        /** @var SimpleCacheBridge $cache */
        $cache = $di->get('cache');
        $cacheKey = sha1(self::class . $text);

        TelegramLog::debug('Проверка наличия в кеше по ключу ' . $cacheKey);
        try {
            $padonakText = $cache->get($cacheKey);
        } catch (InvalidArgumentException $e) {
            $padonakText = null;
        }
        if (!($padonakText)) {
            TelegramLog::debug('Кеш отсуствует, получаем с сервиса и сохраняем');
            $saveCache = true;
            try {
                $client = new Client([
                    'base_uri' => 'https://javer.kiev.ua/',
                    'connect_timeout' => 10,
                    'timeout' => 60
                ]);
                $serviceText = str_replace('_', ' ', $text);
                $response = $client->get(
                    sprintf('alban.php?input=%s', urlencode(mb_convert_encoding($serviceText, 'cp-1251', 'utf-8')))
                );

                if ($responseText = $response->getBody()) {
                    $responseText = mb_convert_encoding($responseText, 'utf-8', 'cp-1251');
                    preg_match('~<h2>.+</h2>\s*<textarea.*>(.*?)</textarea~ism', $responseText, $matches);
                    $text = urldecode(trim($matches[1]));
                }
            } catch (Exception $e) {
                TelegramLog::error($e->getMessage());
                //При ошибке сервиса не надо кешировать!
                $saveCache = false;
            }

            $key = 0;
            $strLen = mb_strlen($text);

            while ($strLen && $key < 1000) {
                $newChar = mb_substr($text, 0, 1);
                if (preg_match('~[a-zа-я]+~i', $newChar)) {
                    if ($key++ % 2 === 0) {
                        $newChar = mb_strtoupper($newChar);
                    }
                }
                $padonakText .= $newChar;
                $text = mb_substr($text, 1, $strLen);
                $strLen = mb_strlen($text);
            }

            if ($saveCache) {
                try {
                    $cache->set($cacheKey, $padonakText, 60 * 60 * 24 * 7);
                } catch (InvalidArgumentException $e) {
                    //ничего не делаем
                }
            }
        }

        $data['text'] = $padonakText;

        return $this->sendAnswerRequest($data);
    }
}
