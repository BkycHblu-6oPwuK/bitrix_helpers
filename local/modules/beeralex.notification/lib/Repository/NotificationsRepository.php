<?php

namespace Beeralex\Notification\Repository;

use Beeralex\Core\Repository\Repository;
use Beeralex\Notification\Contracts\NotificationsRepositoryContract;
use Beeralex\Notification\Tables\NotificationsTable;

class NotificationsRepository extends Repository implements NotificationsRepositoryContract
{
    public function __construct()
    {
        parent::__construct(NotificationsTable::class);
    }

    public function getByChannel(string $channel): array
    {
        return $this->all(['=CHANNEL' => $channel]);
    }

    public function getByStatus(string $status): array
    {
        return $this->all(['=STATUS' => $status]);
    }

    public function getByRecipient(string $recipient): array
    {
        return $this->all(['=RECIPIENT' => $recipient]);
    }

    public function getByCodeId(int $codeId): array
    {
        return $this->all(['=CODE_ID' => $codeId]);
    }

    public function updateStatus(int $id, string $status): void
    {
        $this->update($id, [
            'STATUS' => $status,
            'UPDATED_AT' => new \Bitrix\Main\Type\DateTime(),
        ]);
    }

    public function getNew(): array
    {
        return $this->getByStatus('NEW');
    }

    public function getRecent(int $hours = 24): array
    {
        $date = (new \Bitrix\Main\Type\DateTime())->add("-{$hours} hours");
        return $this->all([
            '>=CREATED' => $date,
        ]);
    }
}
