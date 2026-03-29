<?php

namespace Beeralex\Notification\Repository;

use Beeralex\Core\Repository\Repository;
use Beeralex\Notification\Contracts\EventTypeRepositoryContract;
use Bitrix\Main\Mail\Internal\EventTypeTable;

class EventTypeRepository extends Repository implements EventTypeRepositoryContract
{
    public function __construct()
    {
        parent::__construct(EventTypeTable::class);
    }

    public function getByLanguage(string $language = 'ru', array $select = ['*'], array $order = []): array
    {
        return $this->all(['=LID' => $language], $select, $order);
    }
}
