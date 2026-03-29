<?php

namespace Beeralex\Notification\Repository;

use Beeralex\Core\Repository\Repository;
use Beeralex\Notification\Contracts\NotificationChannelRepositoryContract;
use Beeralex\Notification\Tables\NotificationChannelTable;

class NotificationChannelRepository extends Repository implements NotificationChannelRepositoryContract
{
    public function __construct()
    {
        parent::__construct(NotificationChannelTable::class);
    }

    public function getActiveChannels(): array
    {
        return $this->all(['ACTIVE' => 'Y']);
    }

    public function getNonEmailChannels(): array
    {
        return $this->all(['!CODE' => 'email', 'ACTIVE' => 'Y']);
    }

    public function getByCode(string $code): ?array
    {
        return $this->one(['CODE' => $code]);
    }

    public function activate(int $id): void
    {
        $this->update($id, ['ACTIVE' => 'Y']);
    }

    public function deactivate(int $id): void
    {
        $this->update($id, ['ACTIVE' => 'N']);
    }
}
