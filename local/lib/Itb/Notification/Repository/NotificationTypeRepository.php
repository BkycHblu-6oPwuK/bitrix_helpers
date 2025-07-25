<?php

namespace Itb\Notification\Repository;

use Itb\Notification\Enum\Types;
use Itb\Notification\Tables\NotificationTypeTable;

class NotificationTypeRepository
{
    /**
     * @var NotificationTypeTable|string $entity
     */
    protected readonly string $entity;

    public function __construct()
    {
        $this->entity = NotificationTypeTable::class;
    }

    public function getAll(int $cache = 0): array
    {
        return $this->entity::getList([
            'select' => ['ID', 'CODE', 'NAME'],
            'cache' => ['ttl' => $cache],
            'order' => ['ID' => 'ASC']
        ])->fetchAll();
    }

    public function getByCode(Types $type): ?array
    {
        return $this->entity::getRow([
            'filter' => ['=CODE' => $type->value]
        ]);
    }

    public function add(Types $type, string $name): int
    {
        $result = $this->entity::add([
            'CODE' => $type->value,
            'NAME' => $name
        ]);

        if ($result->isSuccess()) {
            return $result->getId();
        }

        throw new \RuntimeException(implode(', ', $result->getErrorMessages()));
    }
}
