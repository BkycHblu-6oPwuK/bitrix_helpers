<?php

namespace Beeralex\Catalog\Dto;

use Beeralex\Core\Http\Resources\Resource;

/**
 * DTO для торгового предложения с динамическими свойствами.
 * 
 * @property int $id
 * @property int $productId
 * @property bool $active
 * @property bool $available
 * @property array $prices
 * @property array $storesAvailability
 * @property array $allowedStoresAvailability
 */
class OfferDTO extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'id' => (int)$data['ID'],
            'productId' => (int)$data['PRODUCT_ID'],
            'active' => (bool)$data['ACTIVE'],
            'available' => (bool)$data['AVAILABLE'],
            'prices' => $data['PRICES'] ?? [],
            'storesAvailability' => $data['STORES_AVAILABILITY'] ?? [],
            'allowedStoresAvailability' => $data['ALLOWED_STORES_AVAILABILITY'] ?? [],
        ]);
    }
}
