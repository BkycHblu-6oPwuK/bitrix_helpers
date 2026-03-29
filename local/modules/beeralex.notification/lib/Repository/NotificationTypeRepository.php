<?php

namespace Beeralex\Notification\Repository;

use Beeralex\Core\Repository\Repository;
use Beeralex\Notification\Contracts\NotificationTypeRepositoryContract;
use Beeralex\Notification\Tables\NotificationTypeTable;

class NotificationTypeRepository extends Repository implements NotificationTypeRepositoryContract
{
    public function __construct()
    {
        parent::__construct(NotificationTypeTable::class);
    }

    public function getByCode(string $code): ?array
    {
        return $this->one(['=CODE' => $code]);
    }

    public function getAllTypes(): array
    {
        return $this->all([], ['ID', 'CODE', 'NAME'], ['ID' => 'ASC']);
    }

    public function exists(string $code): bool
    {
        return $this->getByCode($code) !== null;
    }

    public function addIfNotExists(string $code, string $name): int
    {
        if ($existing = $this->getByCode($code)) {
            return (int)$existing['ID'];
        }

        return $this->add([
            'CODE' => $code,
            'NAME' => $name,
        ]);
    }
}
