<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Entities\ServerResponse;

/**
 * Start command
 *
 * Gets executed when a user first starts using the bot.
 */
class StartCommand extends UserBaseClass
{

    /**
     * @var string
     */
    protected $name = 'start';

    /**
     * @var string
     */
    protected $description = 'Стартовая команда';

    /**
     * @var string
     */
    protected $usage = '/start';

    /**
     * Show in Help
     *
     * @var bool
     */
    protected $show_in_help = true;

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * @inheritdoc
     */
    public function execute(): ServerResponse
    {
        $message = $this->getMessage();

        $chat_id = $message->getChat()->getId();
        $text = 'Приветствую!' . PHP_EOL . 'Введите /help, чтобы увидеть все команды!';

        $data = [
            'chat_id' => $chat_id,
            'text' => $text,
        ];

        return $this->sendAnswerRequest($data);
    }
}
