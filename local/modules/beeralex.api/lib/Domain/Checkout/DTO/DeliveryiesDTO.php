<?php

namespace Beeralex\Api\Domain\Checkout\DTO;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property string $message
 * @property string $location
 * @property string $city
 * @property string $address
 * @property string $postCode
 * @property DeliveryDTO[] $deliveries
 * @property float $minDeliveryPrice
 * @property string $minDeliveryPriceFormatted
 * @property int $selectedId
 * @property int $storeSelectedId
 * @property float $distance
 * @property float $duration
 * @property string $selectedPvz
 * 
 * DTO доставки с выбранными параметрами
 */
class DeliveryiesDTO extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'message' => $data['message'] ?? '',
            'location' => $data['location'] ?? '',
            'city' => $data['city'] ?? '',
            'address' => $data['address'] ?? '',
            'postCode' => $data['postCode'] ?? '',
            'deliveries' => $data['deliveries'] ?? [],
            'minDeliveryPrice' => $data['minDeliveryPrice'] ?? 0.0,
            'minDeliveryPriceFormatted' => $data['minDeliveryPriceFormatted'] ?? '',
            'selectedId' => $data['selectedId'] ?? 0,
            'storeSelectedId' => $data['storeSelectedId'] ?? 0,
            'selectedPvz' => $data['selectedPvz'] ?? '',
        ]);
    }
}
