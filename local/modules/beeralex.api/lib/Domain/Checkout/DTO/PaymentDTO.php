<?php

namespace Beeralex\Api\Domain\Checkout\DTO;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string $logotip
 * 
 * DTO способа оплаты
 */
class PaymentDTO extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'id' => (int)($data['id'] ?? 0),
            'name' => $data['name'] ?? '',
            'description' => $data['description'] ?? '',
            'logotip' => $data['logotip'] ?? '',
        ]);
    }
}
