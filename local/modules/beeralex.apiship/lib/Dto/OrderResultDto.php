<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Core\Http\Resources\Resource;

/**
 * Результат создания/получения заказа ApiShip.
 *
 * @property int $orderId ID заказа в ApiShip
 * @property string|null $created
 */
final class OrderResultDto extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'orderId' => (int)($data['orderId'] ?? 0),
            'created' => isset($data['created']) ? (string)$data['created'] : null,
        ]);
    }
}
