<?php

declare(strict_types=1);

namespace TGBot\Repositories;

use Longman\TelegramBot\TelegramLog;

class SecretSantaEventMembersRepository extends AbstractRepository
{

    private const TABLE_NAME = 'secret_santa_event_members';

    private static $mapperMaps = [
        'ssem_id' => 'id',
        'ssem_sse_id' => 'eventId',
        'ssem_user_id' => 'userId',
        'ssem_active' => 'active',
        'ssem_coordinate' => 'coordinate',
    ];

    public function searchUserInEvent(int $eventId, int $userId): ?int
    {
        $sth = $this->pdo->prepare('
SELECT `ssem_id`
FROM ' . self::TABLE_NAME . '
WHERE
    `ssem_active` = 1
    AND `ssem_sse_id` = :eventId
    AND `ssem_user_id` = :userId
        ');

        $sth->execute(['eventId' => $eventId, 'userId' => $userId]);
        if ($res = $sth->fetch()) {
            return $res['ssem_id'];
        }

        return null;
    }

    public function addUserInEvent(int $eventId, int $userId): ?int
    {
        $sth = $this->pdo->prepare('
INSERT INTO `secret_santa_event_members`
(`ssem_sse_id`, `ssem_user_id`, `ssem_active`, `ssem_coordinate`)
VALUES
(:eventId, :userId, 1, \'\')
        ');
        if ($sth->execute(['eventId' => $eventId, 'userId' => $userId])) {
            return $this->pdo->lastInsertId();
        }

        return null;
    }

    public function removeUserInEvent(int $eventMemberId): bool
    {
        $sth = $this->pdo->prepare('
DELETE FROM `secret_santa_event_members`
WHERE ssem_id = :eventMemberId
        ');
        return $sth->execute(['eventMemberId' => $eventMemberId]);
    }

    public function updateUserInEvent(int $eventMemberId, array $data): bool
    {

        foreach ($data as $key => $item) {
            $data[$key] = addslashes($item);
        }

        $flipMapper = array_flip(self::$mapperMaps);

        $setArr = [];

        foreach ($data as $key => $item) {
            if (isset($flipMapper[$key])) {
                $setArr[] = sprintf('%s = :%s', $flipMapper[$key], $key) . PHP_EOL;
            }
        }

        $setStr = implode(PHP_EOL . 'AND ', $setArr);

        $sth = $this->pdo->prepare('
UPDATE ' . self::TABLE_NAME . '
SET ' . $setStr . '
WHERE ' . $flipMapper['id'] . ' = :id
        ');

        return $sth->execute(array_merge($data, ['id' => $eventMemberId]));
    }

    public function searchAllUsersInEvent(int $eventId): ?array
    {
        $sth = $this->pdo->prepare('
SELECT *
FROM ' . self::TABLE_NAME . '
WHERE
    ssem_sse_id = :eventId
        ');
        $sth->execute(['eventId' => $eventId]);

        if ($res = $sth->fetchAll()) {
            return $res;
        }

        return null;
    }
}
