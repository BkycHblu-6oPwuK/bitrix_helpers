<?php

namespace Beeralex\Api\Domain\Checkout\DTO;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property string $id
 * @property string $title
 * @property float $price
 * @property string $priceFormatted
 * @property string|int|null $value
 * @property array $values
 * 
 * DTO дополнительной услуги доставки
 */
class ExtraServiceDTO extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'id' => $data['id'] ?? '',
            'title' => $data['title'] ?? '',
            'price' => $data['price'] ?? 0.0,
            'priceFormatted' => $data['priceFormatted'] ?? '',
            'value' => $data['value'] ?? null,
            'values' => $data['values'] ?? [],
        ]);
    }
}
