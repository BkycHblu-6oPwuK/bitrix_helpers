<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Core\Http\Resources\Resource;

/**
 * Статус заказа ApiShip.
 *
 * @property-read int $orderId
 * @property-read string $key Код статуса ApiShip
 * @property-read string $name
 * @property-read string $date
 */
final class StatusDto extends Resource
{
    public function getOrderId(): int
    {
        return (int)$this->getInt('orderId');
    }

    public function getKey(): string
    {
        return (string)$this->getString('key');
    }

    public function getName(): ?string
    {
        return $this->getString('name');
    }

    public function getDate(): ?string
    {
        return $this->getString('date');
    }
}
