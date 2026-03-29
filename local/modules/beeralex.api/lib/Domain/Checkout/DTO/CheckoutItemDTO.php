<?php

namespace Beeralex\Api\Domain\Checkout\DTO;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property int $id
 * @property string $name
 * @property int $quantity
 * @property float $price
 * @property string $priceFormatted
 * @property string $image
 * @property string $url
 * 
 * DTO товара в чекауте
 */
class CheckoutItemDTO extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'id' => $data['id'] ?? 0,
            'name' => $data['name'] ?? '',
            'quantity' => $data['quantity'] ?? 0,
            'price' => $data['price'] ?? 0.0,
            'priceFormatted' => $data['priceFormatted'] ?? '',
            'image' => $data['image'] ?? '',
            'url' => $data['url'] ?? '',
        ]);
    }
}
