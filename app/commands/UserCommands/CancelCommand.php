<?php

declare(strict_types=1);

namespace TGBot\Commands\UserCommands;

use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

class CancelCommand extends UserBaseClass
{

    /**
     * @var string
     */
    protected $name = 'cancel';

    /**
     * @var string
     */
    protected $description = 'Отменить текущий активный разговор';

    /**
     * @var string
     */
    protected $usage = '/cancel';

    /**
     * @var string
     */
    protected $version = '0.2.1';

    /**
     * @var bool
     */
    protected $need_mysql = true;

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * Command execute method
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function execute(): ServerResponse
    {
        $text = 'Нет активного разговора!';

        //Cancel current conversation if any
        $conversation = new Conversation(
            $this->getMessage()->getFrom()->getId(),
            $this->getMessage()->getChat()->getId()
        );

        if ($conversation_command = $conversation->getCommand()) {
            $conversation->cancel();
            $text = 'Разговор "' . $conversation_command . '" отменен!';
        }

        return $this->removeKeyboard($text);
    }

    /**
     * Remove the keyboard and output a text
     *
     * @param string $text
     *
     * @return ServerResponse
     */
    private function removeKeyboard($text): ServerResponse
    {
        return $this->sendAnswerRequest([
            'reply_markup' => Keyboard::remove(['selective' => true]),
            'chat_id'      => $this->getMessage()->getChat()->getId(),
            'text'         => $text,
        ]);
    }

    /**
     * Command execute method if MySQL is required but not available
     *
     * @return ServerResponse
     */
    public function executeNoDb(): ServerResponse
    {
        return $this->removeKeyboard('Нечего отменять.');
    }
}
