<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Core\Http\Resources\Resource;

/**
 * Результат создания/получения заказа ApiShip.
 *
 * @property-read int $orderId ID заказа в ApiShip
 * @property-read string $created
 */
final class OrderResultDto extends Resource
{
    public function getOrderId(): int
    {
        return (int)$this->getInt('orderId');
    }

    public function getCreated(): ?string
    {
        return $this->getString('created');
    }
}
