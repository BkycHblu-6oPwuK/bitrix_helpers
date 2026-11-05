<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Support;

use Beeralex\Apiship\Options;
use Beeralex\Apiship\Support\Concerns\ReadsOrderProperties;
use Bitrix\Main\UserTable;
use Bitrix\Sale\Order;
use Bitrix\Sale\PropertyValueCollectionBase;

class RecipientReader
{
    use ReadsOrderProperties;

    public function __construct(
        private readonly Options $options,
        private readonly OrderAddressReader $address,
    ) {}

    /** @return array<string,mixed> */
    public function read(Order $order): array
    {
        $p = $order->getPropertyCollection();

        return $this->address->read($order) + [
            'contactName' => $this->contactName($p, (int)$order->getUserId()),
            'phone'       => $this->propertyValue($p, $this->options->propPhone),
            'email'       => $this->propertyValue($p, 'EMAIL'),
            'postIndex'   => $this->propertyValue($p, 'ZIP'),
        ];
    }

    protected function contactName(PropertyValueCollectionBase $p, int $userId): string
    {
        $name = $this->propertyValue($p, $this->options->propFio)
            ?? trim(($this->propertyValue($p, 'NAME') ?? '') . ' ' . ($this->propertyValue($p, 'LAST_NAME') ?? ''));

        if ($name === '' && $userId > 0) {
            $user = UserTable::getById($userId)->fetch() ?: [];
            $name = trim(($user['NAME'] ?? '') . ' ' . ($user['LAST_NAME'] ?? ''));
        }

        return $name;
    }
}
