<?php

declare(strict_types=1);

namespace TGBot\Repositories;

class UsersRepository extends AbstractRepository
{

    private const TABLE_NAME = 'user';

    public function getUser(int $userId): ?array
    {
        $sth = $this->pdo->prepare('
SELECT *
FROM ' . self::TABLE_NAME . '
WHERE
    id = :userId
        ');
        $sth->execute(['userId' => $userId]);

        if ($res = $sth->fetch()) {
            return $res;
        }

        return null;
    }
}
