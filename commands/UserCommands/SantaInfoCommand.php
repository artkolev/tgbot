<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Entities\User;
use Longman\TelegramBot\Telegram;
use TGBot\Repositories\SecretSantaEventMembersRepository;
use TGBot\Repositories\SecretSantaEventsRepository;
use TGBot\Repositories\UsersRepository;

class SantaInfoCommand extends UserBaseClass
{

    /**
     * @var string
     */
    protected $name = 'santaInfo';

    /**
     * @var string
     */
    protected $description = 'Информация о текущем статусе "тайного Санты"';

    /**
     * @var string
     */
    protected $usage = '/santaStart';

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
    protected $enabled = false;

    /**
     * If this command needs mysql
     *
     * @var boolean
     */
    protected $need_mysql = true;

    /**
     * Только публичная комманда
     *
     * @var bool
     */
    protected $publicOnly = true;

    /**
     * @var SecretSantaEventsRepository
     */
    private $santaEventsRepository;
    /**
     * @var SecretSantaEventMembersRepository
     */
    private $santaEventMembersRepository;

    public function __construct(Telegram $telegram, Update $update = null)
    {
        parent::__construct($telegram, $update);

        $this->santaEventsRepository = $this->di->get(SecretSantaEventsRepository::class);
        $this->santaEventMembersRepository = $this->di->get(SecretSantaEventMembersRepository::class);
    }

    /**
     * @inheritdoc
     */
    public function execute(): ServerResponse
    {
        $this->logger->info('Новый запрос для информации о тайном Санте');

        $message = $this->getMessage();
        $chat = $message->getChat();

        $data = [
            'chat_id' => $chat->getId(),
            'parse_mode' => 'markdown',
        ];

        if (!$chat->isGroupChat() && !$chat->isSuperGroup()) {
            $this->logger->info('Запрос из ЛС, пропуск.');
            $data['text'] = 'Команда только для чатов. В ЛС данная команда не применима.';
            return $this->sendAnswerRequest($data);
        }

        $this->logger->info('Поиск/создание существующей записи в запущенных событиях');
        if (!($eventId = $this->santaEventsRepository->searchEvent($chat->getId()))) {
            $data['text'] = 'Событие не найдено';
            return $this->sendAnswerRequest($data);
        }
        $this->logger->info('Событие №' . $eventId);

        $this->logger->info('Поиск пользователей, участвующих в данном событии');
        if (
            !($usersInEvent = $this->getEventMembersObjects($eventId))
        ) {
            $data['text'] = 'Ни одного пользоывателя не участвует.';
            return $this->sendAnswerRequest($data);
        }

        $this->logger->info('Создание блока списка для вывода');
        $eventMembersStr = '';

        foreach ($usersInEvent as $userItem) {
            $eventMembersStr .= (PHP_EOL . ' • ' . sprintf(
                '[%s](%s)',
                $userItem->getFirstName() ?
                    trim($userItem->getFirstName() . ' ' . $userItem->getLastName()) :
                    $userItem->getUsername(),
                sprintf('tg://user?id=%s', $userItem->getId())
            ));
        }

        if (empty($eventMembersStr)) {
            $data['text'] = 'Ни одного пользователя не участвует.';
            return $this->sendAnswerRequest($data);
        }

        $data['disable_notification	'] = true;
        $data['disable_web_page_preview'] = true;
        $data['text'] = '*Участники текущего события:*' . PHP_EOL
            . '_Всего участников: ' . count($usersInEvent) . '_' . PHP_EOL
            . $eventMembersStr . PHP_EOL;
        return $this->sendAnswerRequest($data);
    }

    private function getEventMembersObjects(int $eventId): array
    {
        /** @var UsersRepository $UsersRepository */
        $UsersRepository = $this->di->get(UsersRepository::class);

        $eventMembersArr = $this->santaEventMembersRepository
            ->searchAllUsersInEvent($eventId);

        $eventMembersObjects = [];

        foreach ($eventMembersArr as $item) {
            if (!($user = $UsersRepository->getUser($item['ssem_user_id']))) {
                continue;
            }
            $eventMembersObjects[] = new User($user);
        }

        return $eventMembersObjects;
    }
}
