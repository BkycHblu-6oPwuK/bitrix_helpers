<?php

namespace Beeralex\Api\Domain\Checkout\DTO;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property int $id
 * @property string $title
 * @property string $address
 * @property string $phone
 * @property string $schedule
 * @property array $coordinates
 * 
 * DTO магазина для самовывоза
 */
class StoreDTO extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'id' => $data['id'] ?? 0,
            'title' => $data['title'] ?? '',
            'address' => $data['address'] ?? '',
            'phone' => $data['phone'] ?? '',
            'schedule' => $data['schedule'] ?? '',
            'coordinates' => $data['coordinates'] ?? [],
        ]);
    }
}
