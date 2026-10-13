<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Core\Http\Resources\Resource;

/**
 * Статус заказа ApiShip.
 *
 * @property int $orderId
 * @property string $key Код статуса ApiShip
 * @property string|null $name
 * @property string|null $date
 */
final class StatusDto extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'orderId' => (int)($data['orderId'] ?? 0),
            'key'     => (string)($data['key'] ?? ''),
            'name'    => isset($data['name']) ? (string)$data['name'] : null,
            'date'    => isset($data['date']) ? (string)$data['date'] : null,
        ]);
    }
}
