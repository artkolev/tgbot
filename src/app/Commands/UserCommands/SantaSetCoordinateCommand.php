<?php

declare(strict_types=1);

namespace TGBot\Commands\UserCommands;

use Longman\TelegramBot\Conversation;
use Longman\TelegramBot\Entities\Chat;
use Longman\TelegramBot\Entities\Keyboard;
use Longman\TelegramBot\Entities\Message;
use Longman\TelegramBot\Entities\ServerResponse;
use Longman\TelegramBot\Entities\Update;
use Longman\TelegramBot\Exception\TelegramException;
use Longman\TelegramBot\Request;
use Longman\TelegramBot\Telegram;
use TGBot\Repositories\SecretSantaEventMembersRepository;
use TGBot\Repositories\SecretSantaEventsRepository;

class SantaSetCoordinateCommand extends UserBaseClass
{

    /**
     * @var string
     */
    protected $name = 'santaSetCoordinate';

    /**
     * @var string
     */
    protected $description = 'Указать свои координаты для участия в "тайном Cанте"';

    /**
     * @var string
     */
    protected $usage = '/santaSetCoordinate';

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
     * Make sure this command only executes on a private chat.
     *
     * @var bool
     */
    protected $private_only = true;

    /**
     * @var SecretSantaEventsRepository
     */
    private $santaEventsRepository;
    /**
     * @var SecretSantaEventMembersRepository
     */
    private $santaEventMembersRepository;

    /**
     * @var Message
     */
    private $realMessage;

    /**
     * @var Chat
     */
    private $eventChat;

    /**
     * @var int
     */
    private $eventId;

    /**
     * @var int
     */
    private $eventMemberId;

    public function __construct(Telegram $telegram, Update $update = null)
    {
        parent::__construct($telegram, $update);

        $this->santaEventsRepository = $this->di->get(SecretSantaEventsRepository::class);
        $this->santaEventMembersRepository = $this->di->get(SecretSantaEventMembersRepository::class);
    }


    /**
     * @param Chat $eventChat
     */
    public function setEventChat(Chat $eventChat): void
    {
        $this->eventChat = $eventChat;
    }

    /**
     * @return Chat
     */
    public function getEventChat(): ?Chat
    {
        return $this->eventChat;
    }

    /**
     * @param int $eventId
     */
    public function setEventId(int $eventId): void
    {
        $this->eventId = $eventId;
    }

    /**
     * @return int
     */
    public function getEventId(): ?int
    {
        return $this->eventId;
    }

    /**
     * @param Message $realMessage
     */
    public function setRealMessage(Message $realMessage): void
    {
        $this->realMessage = $realMessage;
    }


    /**
     * @return Message
     */
    public function getRealMessage(): ?Message
    {
        return $this->realMessage;
    }

    /**
     * @param int $eventMemberId
     */
    public function setEventMemberId(int $eventMemberId): void
    {
        $this->eventMemberId = $eventMemberId;
    }

    /**
     * @return int
     */
    public function getEventMemberId(): ?int
    {
        return $this->eventMemberId;
    }

    /**
     * @inheritdoc
     */
    public function execute(): ServerResponse
    {
        $this->logger->info('Новый запрос для установки адреса в тайном Санте');
        $this->setRealMessage($this->getMessage());
        return $this->sendPrivateConfirmCoordinate();
    }

    public function sendPrivateConfirmCoordinate(): ServerResponse
    {
        $this->logger->info('Создание беседы с пользователем');

        $result = Request::emptyResponse();

        if (!$this->getRealMessage()) {
            return $result;
        }

        $text = trim($this->getRealMessage()->getText(true));

        $data = [
            'chat_id' => $this->getRealMessage()->getFrom()->getId(),
            'parse_mode' => 'markdown',
        ];

        try {
            $conversation = new Conversation(
                $this->getRealMessage()->getFrom()->getId(),
                $this->getRealMessage()->getFrom()->getId(),
                $this->getName()
            );

            $notes = &$conversation->notes;
            !is_array($notes) && $notes = [];

            //cache data from the tracking session if any
            $state = $notes['state'] ?? 0;
            if (isset($notes['eventChat'])) {
                $this->setEventChat(new Chat($notes['eventChat']));
            }
            if (isset($notes['eventId'])) {
                $this->setEventId($notes['eventId']);
            }
            if (isset($notes['eventMemberId'])) {
                $this->setEventMemberId($notes['eventMemberId']);
            }

            $yesNoKeyboard = new Keyboard(
                [
                    'keyboard' => [['Да', 'Нет']],
                    'resize_keyboard' => true,
                    'one_time_keyboard' => true,
                    'selective' => true,
                ]
            );

            switch ($state) {
                case 0:
                    if (
                        $text === ''
                        && $this->getEventChat()
                        && $this->getEventId()
                        && $this->getEventMemberId()
                    ) {
                        $notes['state'] = 0;
                        $notes['eventChat'] = $this->getEventChat();
                        $notes['eventId'] = $this->getEventId();
                        $notes['eventMemberId'] = $this->getEventMemberId();
                        $conversation->update();

                        $data['text'] = sprintf(
                            'Для подтверждения участия в мероприятии *"Тайный санта"* в чате %s, пожалуйста, '
                            . 'сообщите свое _полное фамилию, имя, отчество, '
                            . 'индекс, почтовый адрес и контактный телефон_ ответом на это сообщение. '
                            . 'При желании можете указать свои комментарии к подарку.',
                            $this->getChatLink($this->getEventChat())
                        );
                        $data['reply_markup'] = Keyboard::remove(['selective' => true]);

                        $this->logger->info(
                            'Отправка подтверждающего сообщения участнику №'
                            . $this->getRealMessage()->getFrom()->getId(),
                            $data
                        );
                        $result = Request::sendMessage($data);
                        break;
                    }

                    $notes['coordinate'] = $text;
                    $text = '';
                    // no break

                case 1:
                    if ($text === '' && !empty($notes['coordinate'])) {
                        $notes['state'] = 1;
                        $notes['eventChat'] = $this->getEventChat();
                        $notes['eventId'] = $this->getEventId();
                        $notes['eventMemberId'] = $this->getEventMemberId();
                        $conversation->update();

                        $data['text'] = 'Подтвердите свои данные:' . PHP_EOL . $notes['coordinate'];
                        $data['reply_markup'] = $yesNoKeyboard;

                        $this->logger->info(
                            'Отправка запроса на подтверждение',
                            $data
                        );

                        $result = Request::sendMessage($data);
                        break;
                    }

                    $notes['post_message'] = ($text === 'Да');
                    $text = '';
                // no break

                case 2:
                    $data['reply_markup'] = Keyboard::remove(['selective' => true]);

                    $this->logger->info(
                        'Сохранение данных',
                        $data
                    );
                    if ($text === '' && $notes['post_message']) {
                        $data['parse_mode'] = 'markdown';
                        $data['text'] = $this->saveCoordinateData($notes['coordinate']);
                    } else {
                        $data['text'] = 'Сохранение прервано..';
                    }

                    $conversation->stop();
                    $this->logger->info(
                        'передача итоговых данных',
                        $data
                    );
                    $result = $this->sendAnswerRequest($data);
            }

            //Необходимо остановить разговор, если что-то пошло не так
            if (!$result->isOk()) {
                $conversation->stop();
            }
        } catch (TelegramException $e) {
            $this->logger->error('Ошибка запроса', [$e->getMessage()]);
        }

        return $result;
    }

    private function saveCoordinateData(string $coordinate): string
    {
        $this->logger->info('Сохранение информации участника', [$coordinate]);

        return $this->santaEventMembersRepository
            ->updateUserInEvent($this->getEventMemberId(), ['coordinate' => addslashes($coordinate)]) ?
            'Информация успешно сохранена' :
            'При сохранении произошла ошибка';
    }

    private function getChatLink(Chat $chat): string
    {
        if ($chat->getInviteLink()) {
            return sprintf(
                '[%s](%s)',
                $chat->getTitle(),
                $chat->getInviteLink()
            );
        }

        if ($chat->getUsername()) {
            return sprintf(
                '[%s](https://t.me/%s)',
                $chat->getTitle(),
                $chat->getUsername()
            );
        }

        return '*' . $chat->getTitle() . '*';
    }
}
