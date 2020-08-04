<?php

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Entities\ServerResponse;

/**
 * User "/slap" command
 *
 * Slap a user around with a big trout!
 */
class SlapCommand extends UserBaseClass
{

    private static $PHRASES = [
        '%s слегка шлепает %s по лицу большой форелью.',
        '%s дает пощечину %s огромной щукой с такими же огромными зубами.',
        '%s заставляет своего любимца бобра шлепнуть %s хвостом.',
        '%s шлепает %s широким боком своего "меча".',
        '%s шлепает %s очень большим папоротником.',
        '%s дает %s хорошую оплеуху своей лапшой.',
        '%s берет пальмовую ветвь и шлепает %s, пока она не становится совсем зеленой и мягкой.',
        '%s хлопает %s концом силового кабеля 1200В.',
        '%s шлепает %s очень скользкой лозой.',
        '%s щелкает %s огромным утиным пером.',
        '%s шлепает %s банджи-шнуром.',
        '%s тупо лупит %s.',
        '%s шлепает банхамером %s, но банхамерка еще не выросла.',
        '%s дает пощечину %s установочным диском Шindows 7.',
        '%s укокошивает %s Макбуком.',
        '%s бьет %s стаканом воды, разливая воду повсюду.',
        '%s вбивает в жопу %s дорожный конус.',
        '%s дает пощечину %s',
        '%s зовет ментов, те лупасят %s дубинками.',
        '%s сильно шлепает %s и получает в ответ в челюсть.',
        '%s шлепает %s бутылкой из-под кока-колы, отчего та взрывается.',
        '%s влепляет по физиономии %s',
        '%s отвешивает оплеуху %s',
        '%s отвешивает затрещину %s',
        '%s лупит армейской бляхой по заднице %s',

        '%s садит на бутылку %s',
        '%s жестко страпонит %s',

        //@tooz151
        '%s делает апперкот в челюсть %s',
        '%s сажает %s на шар с членом',
        '%s сажает %s на дилдошар',
        '%s хватает секретаршу за ногу и бьет ей %s по голове',
        '%s пиздит %s кочергой по жопе',

        //@Viveya_Witch
        '%s бьёт линейкой %s по жопке',
        '%s бьёт %s по темечку тухлым яйцом',
        '%s лупит по губам %s искусственным членом',
        '%s решил использовать %s вместо звёзды на ёлке',

        //@comewithme7
        '%s разбивает кафельную плитку о голову %s',
        '%s с особой жестокостью избивает торшером %s',

    ];

    /**
     * @var string
     */
    protected $name = 'slap';

    /**
     * @var string
     */
    protected $description = 'Пощечина кому-то по имени пользователя';

    /**
     * @var string
     */
    protected $usage = '/slap <@user>';

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
    protected $replyToSender = true;

    /**
     * Command execute method
     *
     * @return ServerResponse
     */
    public function execute(): ServerResponse
    {
        $this->logger->info('Новый запрос пощечины');

        $message = $this->getMessage();

        $data = [
            'chat_id' => $this->getMessage()->getChat()->getId(),
            'parse_mode' => 'markdown',
        ];

        $targetLnk = null;
        if (!($senderLnk = $this->getSenderLink()) || !($targetLnk = $this->getTargetLink())) {
            $this->logger->error('Не получены адресаты запроса', [$senderLnk, $targetLnk, $message->toJson()]);
            $data['text'] = 'Жаль, но некому дать пощечину.. Смотри `/help slap`.';
            return $this->sendAnswerRequest($data);
        }

        $this->replyToSender = false;
        $data['disable_web_page_preview'] = true;
        $phraseKey = array_rand(self::$PHRASES);
        $this->logger->info('Выбрана фраза №' . $phraseKey);
        $data['text'] = sprintf(self::$PHRASES[$phraseKey], $senderLnk, $targetLnk);

        return $this->sendAnswerRequest($data);
    }
}
