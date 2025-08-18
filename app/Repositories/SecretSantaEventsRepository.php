<?php

declare(strict_types=1);

namespace TGBot\Repositories;

use DateTime;

class SecretSantaEventsRepository extends AbstractRepository
{

    private const TABLE_NAME = 'secret_santa_events';

    public function searchEvent(int $chatId): ?int
    {
        $sth = $this->pdo->prepare('
SELECT `sse_id`
FROM ' . self::TABLE_NAME . '
WHERE
    `sse_active` = 1
    AND (
        `sse_last_date` IS NULL
        OR `sse_last_date` > NOW()
    )
    AND sse_chat_id = :chatId
        ');
        $sth->execute(['chatId' => $chatId]);

        if ($res = $sth->fetch()) {
            return $res['sse_id'];
        }

        return null;
    }

    public function createEvent(int $chatId, DateTime $lastDate = null): ?int
    {
        $sth = $this->pdo->prepare('
INSERT INTO ' . self::TABLE_NAME . '
(`sse_chat_id`, `sse_active`, `sse_last_date`)
VALUES
(:chatId, 1, \':lastDate\')
        ');

        if (
            $sth->execute([
            'chatId' => $chatId,
            'lastDate' => $lastDate ? $lastDate->format('Y-m-d H:i:s') : 'DATE_ADD(CURDATE(), INTERVAL 1 MONTH)',
            ])
        ) {
            return $this->pdo->lastInsertId();
        }

        return null;
    }
}
