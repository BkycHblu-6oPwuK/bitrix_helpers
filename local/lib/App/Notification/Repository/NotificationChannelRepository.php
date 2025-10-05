<?php
namespace App\Notification\Repository;

use App\Notification\Enum\Channels;
use App\Notification\Tables\NotificationChannelTable;

class NotificationChannelRepository
{
    /**
     * @var NotificationChannelTable|string $entity
     */
    protected readonly string $entity;

    public function __construct()
    {
        $this->entity = NotificationChannelTable::class;
    }
    
    public function getAll(int $cache = 0): array
    {
        return $this->entity::getList([
            'select' => ['ID', 'CODE', 'NAME'],
            'cache' => ['ttl' => $cache],
            'order' => ['ID' => 'ASC']
        ])->fetchAll();
    }

    public function getByCode(Channels $channel): ?array
    {
        return $this->entity::getRow([
            'filter' => ['=CODE' => $channel->value]
        ]);
    }

    /**
     * @throws \RuntimeException
     */
    public function add(Channels $channel, string $name): int
    {
        $result = $this->entity::add([
            'CODE' => $channel->value,
            'NAME' => $name
        ]);

        if ($result->isSuccess()) {
            return $result->getId();
        }

        throw new \RuntimeException(implode(', ', $result->getErrorMessages()));
    }
}
