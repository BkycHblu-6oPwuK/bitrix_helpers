<?php

namespace Beeralex\Api\Domain\Checkout\DTO;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property string $id
 * @property string $name
 * @property string $description
 * @property string $logo
 * 
 * DTO способа оплаты
 */
class PaymentDTO extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'id' => $data['id'] ?? '',
            'name' => $data['name'] ?? '',
            'description' => $data['description'] ?? '',
            'logo' => $data['logo'] ?? '',
        ]);
    }
}
