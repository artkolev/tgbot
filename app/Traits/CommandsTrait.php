<?php

declare(strict_types=1);

namespace TGBot\Traits;

use Longman\TelegramBot\Commands\Command;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\TelegramLog;
use TGBot\Repositories\UserChatRepository;

/** @var Command $this */
trait CommandsTrait
{

    /**
     * Pre-execute command
     *
     * @return ServerResponse
     * @throws TelegramException
     */
    public function preExecute(): ServerResponse
    {
        /** @var Message $message */
        $message = $this->getMessage();
        TelegramLog::debug('message: ' . $message->toJson());

        //Временно
        if (!empty($message->getEntities())) {
            TelegramLog::debug('entities: ' . json_encode($message->getEntities()));
        }

        if (
            method_exists($this, 'isPublicOnly')
            && $this->isPublicOnly()
            && $message->getChat()->isPrivateChat()
        ) {
            TelegramLog::debug('message only chat');
            if (
                ($user = $message->getFrom())
                && ($res = Request::sendMessage([
                    'chat_id' => $user->getId(),
                    'parse_mode' => 'Markdown',
                    'text' => sprintf(
                        "/%s Команда только для чатов. В личных сообщениях данная команда не применима.\n(`%s`)",
                        $this->getName(),
                        $message->getText()
                    ),
                ]))
                && $res->getOk()
            ) {
                return $res;
            }

            return Request::emptyResponse();
        }

        if ($this->isNeedPrivateAccess() && !$this->checkPrivateAccess($message)) {
            TelegramLog::info('No private access');
            if (
                ($res = $this->sendAnswerRequest(
                    [
                        'chat_id' => $message->getChat()->getId(),
                        'parse_mode' => 'Markdown',
                        'reply_to_message_id' => $message->getMessageId(),
                        'disable_web_page_preview' => true,
                        'text' => sprintf(
                            'Бот не имеет доступа к сообщениям пользователя! ' . PHP_EOL
                            . 'Отправьте *личным сообщением* для [Artkolev Bot](https://t.me/%s) команду `/start`',
                            $this->telegram->getBotUsername()
                        ),
                    ]
                ))
                && $res->getOk()
            ) {
                return $res;
            }

            return Request::emptyResponse();
        }

        /** @noinspection PhpUndefinedClassInspection */
        return parent::preExecute();
    }

    /**
     * @param array $data
     * @return ServerResponse
     */
    protected function sendAnswerRequest(array $data): ServerResponse
    {
        $message = $this->getMessage();
        $chat = $message->getChat();

        if (
            $this->isReplyToSender()
            && ($chat->isGroupChat() || $chat->isSuperGroup())
        ) {
            $data['reply_to_message_id'] = $message->getMessageId();
        }
        TelegramLog::info('send message', $data);
        try {
            return Request::sendMessage($data);
        } catch (TelegramException $e) {
            TelegramLog::error('Error send message', ['message' => $e->getMessage()]);
            return Request::emptyResponse();
        }
    }

    /**
     * @param Message $message
     * @return bool
     */
    protected function checkPrivateAccess(Message $message): bool
    {
        /** @var UserChatRepository $userChatRepository */
        $userChatRepository = $this->di->get(UserChatRepository::class);

        return $userChatRepository->issetUser($message->getFrom()->getId(), $message->getFrom()->getId());
    }

    /**
     * Является ли команда только публичной
     *
     * @return bool
     */
    public function isPublicOnly(): bool
    {
        return $this->publicOnly ?? false;
    }

    /**
     * Нужен ли приватный доступ комманде
     * @return bool
     */
    public function isNeedPrivateAccess(): bool
    {
        return $this->needPrivateAccess ?? false;
    }

    /**
     *  Ответить тому, кто спросил
     * @return bool
     */
    public function isReplyToSender(): bool
    {
        return $this->replyToSender ?? false;
    }
}
