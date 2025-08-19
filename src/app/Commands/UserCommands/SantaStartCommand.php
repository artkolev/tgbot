<?php

declare(strict_types=1);

namespace TGBot\Commands\UserCommands;

use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use TGBot\Repositories\SecretSantaEventMembersRepository;
use TGBot\Repositories\SecretSantaEventsRepository;

class SantaStartCommand extends UserBaseClass
{

    /**
     * @var string
     */
    protected $name = 'santaStart';

    /**
     * @var string
     */
    protected $description = 'Подписаться на участие в "тайном Санте"';

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
     * Только публичная команда
     *
     * @var bool
     */
    protected $publicOnly = true;

    /**
     * Требуется приватный доступ
     *
     * @var bool
     */
    protected $needPrivateAccess = true;

    /**
     * Ответить отправителю команды
     *
     * @var bool
     */
    protected $replyToSender = true;

    /**
     * @var Chat
     */
    private $chat;

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

        $this->logger->info('Новый запрос для участия в тайном Санте');

        $message = $this->getMessage();
        $this->chat = $message->getChat();

        $data = [
            'chat_id' => $this->chat->getId(),
            'parse_mode' => 'markdown',
        ];

        if (!$this->chat->isGroupChat() && !$this->chat->isSuperGroup()) {
            $this->logger->info('Запрос из ЛС, пропуск.');
            $data['text'] = 'Команда только для чатов. В ЛС данная команда не применима.';
            return $this->sendAnswerRequest($data);
        }

        $this->logger->info('Поиск/создание существующей записи в запущенных событиях');
        if (!$eventId = $this->searchOrCreateEvent()) {
            $data['text'] = 'Что-то пошло не так :-(';
            return $this->sendAnswerRequest($data);
        }
        $this->logger->info('Событие №' . $eventId);

        $this->logger->info('Поиск пользователя, участвующем в данном событии');
        if ($this->santaEventMembersRepository->searchUserInEvent($eventId, $message->getFrom()->getId())) {
            $data['text'] = 'Вы уже участвуете в данном событиии.';
            return $this->sendAnswerRequest($data);
        }

        $this->logger->info('Регистрация нового участника');
        if (
            $eventMemberId = $this->santaEventMembersRepository
                ->addUserInEvent($eventId, $message->getFrom()->getId())
        ) {
            //Вызов класса диалога с пользователем, чтобы не заставлять его вводить комманды вручную
            $coordinateCommand = new SantaSetCoordinateCommand($this->getTelegram(), $this->getUpdate());
            $coordinateCommand->setRealMessage($this->getMessage());
            $coordinateCommand->setEventChat($this->chat);
            $coordinateCommand->setEventId($eventId);
            $coordinateCommand->setEventMemberId($eventMemberId);

            if (!$coordinateCommand->sendPrivateConfirmCoordinate()->isOk()) {
                $this->logger->info('Бот не имеет доступа к сообщениям участника');
                $this->logger->info('Удаление участника');
                $this->santaEventMembersRepository->removeUserInEvent($eventMemberId);
                $data['disable_web_page_preview'] = true;
                $result = Request::getMe();
                $data['text'] = 'Бот не имеет доступа к сообщениям пользователя! ' . PHP_EOL
                    . 'Отправте *личным сообщением* для '
                    . sprintf(
                        '[%s](https://t.me/%s)',
                        $result->isOk() ? $result->getResult()->getFirstName() : $this->telegram->getBotUsername(),
                        $result->isOk() ? $result->getResult()->getUsername() : $this->telegram->getBotUsername()
                    )
                    . ' команду `/start`';
                return $this->sendAnswerRequest($data);
            }

            $this->logger->info('Участник успешно добавлен');
            $data['text'] = 'Поздравляем! Вы зарегистрировали свое участие!';
            return $this->sendAnswerRequest($data);
        }

        $data['text'] = 'Что-то пошло не так :-(';
        return $this->sendAnswerRequest($data);
    }

    private function searchOrCreateEvent(): ?int
    {
        if ($eventId = $this->santaEventsRepository->searchEvent($this->chat->getId())) {
            $this->logger->info('Событие уже существует');
            return $eventId;
        }

        $this->logger->info('Создание нового события');
        return $this->santaEventsRepository->createEvent($this->chat->getId());
    }
}
