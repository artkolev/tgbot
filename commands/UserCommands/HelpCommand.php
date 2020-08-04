<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;

/**
 * User "/help" command
 *
 * Command that lists all available commands and displays them in User and Admin sections.
 */
class HelpCommand extends UserBaseClass
{

    /**
     * @var string
     */
    protected $name = 'help';

    /**
     * @var string
     */
    protected $description = 'Справка по командам бота';

    /**
     * @var string
     */
    protected $usage = '/help или /help <команда>';

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
     * Требуется приватный доступ
     *
     * @var bool
     */
    protected $needPrivateAccess = true;

    /**
     * @var Message $message
     */
    private $message;

    /**
     * @inheritdoc
     */
    public function execute(): ServerResponse
    {
        $message = $this->getMessage();
        $chatId = $message->getChat()->getId();
        $commandStr = trim($message->getText(true));

        // Admin commands shouldn't be shown in group chats
        $safeToShow = $message->getChat()->isPrivateChat();

        $data = [
            'chat_id' => $chatId,
            'parse_mode' => 'markdown',
        ];

        list($allCommands, $userCommands, $adminCommands) = $this->getUserAdminCommands();

        // If no command parameter is passed, show the list.
        if ($commandStr === '') {
            $data['text'] = '*Список команд*:' . PHP_EOL;
            foreach ($userCommands as $userCommand) {
                $data['text'] .= '/' . $userCommand->getName() . ' - ' . $userCommand->getDescription() . PHP_EOL;
            }

            if ($safeToShow && count($adminCommands) > 0) {
                $data['text'] .= PHP_EOL . '*Список админских команд*:' . PHP_EOL;
                foreach ($adminCommands as $adminCommand) {
                    $data['text'] .= '/' . $adminCommand->getName() . ' - ' . $adminCommand->getDescription() . PHP_EOL;
                }
            }

            $data['text'] .= PHP_EOL . 'Для подробностей по команде: /help <команда>';

            return $this->sendAnswerRequest($data);
        }

        $commandStr = str_replace('/', '', $commandStr);
        if (isset($allCommands[$commandStr]) && ($safeToShow || !$allCommands[$commandStr]->isAdminCommand())) {
            $command = $allCommands[$commandStr];
            $data['text'] = sprintf(
                'Команда: %s (v%s)' . PHP_EOL .
                'Описание: %s' . PHP_EOL .
                'Использование: %s',
                $command->getName(),
                $command->getVersion(),
                $command->getDescription(),
                $command->getUsage()
            );

            return $this->sendAnswerRequest($data);
        }

        $data['text'] = 'Помощь не доступна: Команда /' . $commandStr . ' не найдена';

        return $this->sendAnswerRequest($data);
    }

    /**
     * Get all available User and Admin commands to display in the help list.
     *
     * @return Command[][]
     * @throws TelegramException
     */
    protected function getUserAdminCommands(): array
    {
        // Only get enabled Admin and User commands that are allowed to be shown.
        /** @var Command[] $commands */
        $commands = array_filter($this->telegram->getCommandsList(), function ($command) {
            /** @var Command $command */
            return !$command->isSystemCommand() && $command->showInHelp() && $command->isEnabled();
        });

        $userCommands = array_filter($commands, function ($command) {
            /** @var Command $command */
            if (
                !$command->isUserCommand()
                || ($command->isPrivateOnly() && !$this->getMessage()->getChat()->isPrivateChat())
                || (method_exists($command, 'isPublicOnly')
                    && $command->isPublicOnly() && $this->getMessage()->getChat()->isPrivateChat())
            ) {
                return false;
            }

            return true;
        });

        $adminCommands = array_filter($commands, function ($command) {
            /** @var Command $command */
            return $command->isAdminCommand();
        });

        ksort($commands);
        ksort($userCommands);
        ksort($adminCommands);

        return [$commands, $userCommands, $adminCommands];
    }
}
