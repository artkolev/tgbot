<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Doctrine\DBAL\Driver\Connection;
use Doctrine\ORM\EntityManager;
use League\Container\Container;
use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Telegram;
use Monolog\Logger;
use TGBot\Factories\ServiceProviderFactory;
use TGBot\Traits\CommandsTrait;

abstract class UserBaseClass extends UserCommand
{
    use CommandsTrait;

    /**
     * @var Container
     */
    protected $di;

    /**
     * @var EntityManager
     */
    protected $entityManager;

    /**
     * @var Connection
     */
    protected $pdo;
    /**
     * @var Logger
     */
    protected $logger;

    public function __construct(Telegram $telegram, Update $update = null)
    {
        parent::__construct($telegram, $update);

        $this->di = ServiceProviderFactory::build();
        $this->logger = $this->di->get('logger');
        $this->entityManager = $this->di->get('entityManager');
        $this->pdo = $this->entityManager->getConnection()->getWrappedConnection();
    }

    /**
     * Получение сссылки отправителя
     * @return string|null
     */
    protected function getSenderLink(): ?string
    {
        $this->logger->info('Получение имени отправителя');
        /** @var User $from */
        if (!$from = $this->getMessage()->getFrom()) {
            $this->logger->info('Именя отправителя не найдено!');
            return null;
        }

        $this->logger->info('Формирование ссылки призыва');
        return sprintf(
            '[%s](%s)',
            $from->getFirstName() ? trim($from->getFirstName() . ' ' . $from->getLastName()) : $from->getUsername(),
            sprintf('tg://user?id=%s', $from->getId())
        );
    }

    /**
     * Получение ссылки призыва получателя
     * @return string|null
     */
    protected function getTargetLink(): ?string
    {
        $this->logger->info('Получение имени получателя');
        $text = $this->getMessage()->getText();
        $entities = $this->getMessage()->getEntities();

        if (count($entities) <= 1) {
            $this->logger->info('Нет вложенных объектов сообщения');
            return $this->getBasicTargetLink();
        }

        $minOffset = 0;
        $targetLnk = null;
        $this->logger->info('Проход по объектам сообщения, поиск имени получателя');
        foreach ($entities as $entity) {
            switch ($entity->getType()) {
                case 'bot_command':
                    $this->logger->info('Найден объект команды, установка минимального отступа');
                    $minOffset = $entity->getOffset();
                    continue 2;
                    break;

                case 'mention':
                    $this->logger->info('Найден объект призыва по имени, создание ссылки через имя пользователя');
                    if ($entity->getOffset() < $minOffset) {
                        $this->logger->error('Объект не попадает под ограничение отступа');
                        continue 2;
                    }
                    $targetUser = mb_substr($text, $entity->getOffset(), $entity->getLength());
                    if (!preg_match('~^@([\w_]{5,})$~', $targetUser, $matches)) {
                        $this->logger->error('Невозможно получить имя пользователя');
                        continue 2;
                    }
                    $targetLnk = sprintf('[%s](https://t.me/%s)', $targetUser, $matches[1]);
                    break 2;
                    break;

                case 'text_mention':
                    $this->logger->info('Найден объект призыва по id, создание ссылки через tg//');
                    if (!$targetUser = $entity->getUser()) {
                        $this->logger->error('Невозможно получить объект пользователя');
                        continue 2;
                    }
                    $targetLnk = sprintf(
                        '[%s](%s)',
                        $targetUser->getFirstName() ?
                            trim($targetUser->getFirstName() . ' ' . $targetUser->getLastName()) :
                            $targetUser->getUsername(),
                        sprintf('tg://user?id=%s', $targetUser->getId())
                    );
            }
        }
        $this->logger->info('Сформирована ссылка обращения к получателю ' . $targetLnk);

        return $targetLnk ?? $this->getBasicTargetLink();
    }

    /**
     * Получение ссылки на призыв пользователя без объекта призыва
     * @return string|null
     */
    protected function getBasicTargetLink(): ?string
    {
        if (preg_match('~^@([\w_]{5,})~', trim($this->getMessage()->getText(true)), $matches)) {
            return sprintf('[@%s](https://t.me/%s)', $matches[1], $matches[1]);
        }

        /** @var Message $replyMessage */
        /** @var User $replyFrom */
        if (
            ($replyMessage = $this->getMessage()->getReplyToMessage())
            && ($replyFrom = $replyMessage->getFrom())
            && ($replyFrom->getId() !== $this->getMessage()->getFrom()->getId())
        ) {
            return sprintf(
                '[%s](%s)',
                $replyFrom->getFirstName() ?
                    trim($replyFrom->getFirstName() . ' ' . $replyFrom->getLastName()) :
                    $replyFrom->getUsername(),
                sprintf('tg://user?id=%s', $replyFrom->getId())
            );
        }

        return null;
    }
}
