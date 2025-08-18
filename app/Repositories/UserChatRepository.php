<?php

declare(strict_types=1);

namespace TGBot\Repositories;

class UserChatRepository extends AbstractRepository
{

    private const TABLE_NAME = 'user_chat';

    /**
     * Проверка существования пользователя в чате
     *
     * @param int $userId
     * @param int $chatId
     * @return bool
     */
    public function issetUser(int $userId, int $chatId): bool
    {

        $sth = $this->pdo->prepare('
SELECT COUNT(`user_id`) = 1 as user_isset
FROM ' . self::TABLE_NAME . '
WHERE `user_id` = :userId
    AND `chat_id` = :chatId
        ');

        $sth->execute(['userId' => $userId, 'chatId' => $chatId]);

        if ($res = $sth->fetch()) {
            return (int)$res['user_isset'] === 1;
        }

        return false;
    }
}
