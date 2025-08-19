<?php

declare(strict_types=1);

namespace TGBot\Factories;

use Longman\TelegramBot\Telegram;

class TelegramFactory
{
    public static function build()
    {
        $telegram = new Telegram(getenv('BOT_API_KEY'), getenv('BOT_USERNAME'));
        if (getenv('ADMIN_USER')) {
            $telegram->enableAdmin((int)getenv('ADMIN_USER'));
        }
        $telegram->enableLimiter();
        $telegram->setDownloadPath(realpath(__DIR__ . '/../../www/_download/'));
        $telegram->setUploadPath(realpath(__DIR__ . '/../../www/_upload/'));
        if (getenv('GOOGLE_API_KEY')) {
            $telegram->setCommandConfig('date', ['google_api_key' => getenv('GOOGLE_API_KEY')]);
        }
        //$telegram->setWebhook(getenv('BOT_HOOK_URL') . getenv('BOT_API_KEY') . '/');

        return $telegram;
    }
}
