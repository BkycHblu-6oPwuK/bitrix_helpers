<?php

namespace Beeralex\Notification\Repository;

use Beeralex\Core\Repository\Repository;
use Beeralex\Notification\Contracts\NotificationTemplateLinkRepositoryContract;
use Beeralex\Notification\Tables\NotificationTemplateLinkTable;

class NotificationTemplateLinkRepository extends Repository implements NotificationTemplateLinkRepositoryContract
{
    public function __construct()
    {
        parent::__construct(NotificationTemplateLinkTable::class);
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
