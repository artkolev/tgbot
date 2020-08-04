<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Entities\ServerResponse;

/**
 * User "/slap" command
 *
 * Slap a user around with a big trout!
 */
class SpinCommand extends UserBaseClass
{
    /**
     * @var string
     */
    protected $name = 'spin';

    /**
     * @var string
     */
    protected $description = 'Крутим бутылочку';

    /**
     * @var string
     */
    protected $usage = '/spin';

    /**
     * Только публичная команда
     *
     * @var bool
     */
    protected $publicOnly = true;

    /**
     * Ответить отправителю команды
     *
     * @var bool
     */
    protected $replyToSender = false;

    /**
     * Command execute method
     *
     * @return ServerResponse
     */
    public function execute(): ServerResponse
    {
        $this->logger->info('Новый запрос бутылочки');

        //$message = $this->getMessage();

        $data = [
            'chat_id' => $this->getMessage()->getChat()->getId(),
            'parse_mode' => 'markdown',
        ];

        $data['text'] = 'Тест';

        return $this->sendAnswerRequest($data);
    }
}
