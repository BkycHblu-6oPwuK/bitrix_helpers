<?php

namespace Beeralex\Api\Domain\Checkout\DTO;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property string $message
 * @property string $location
 * @property string $city
 * @property string $address
 * @property string $postCode
 * @property string $selectedPvz
 * @property array $coordinates
 * @property string $completionDate
 * @property DeliveryDTO[] $deliveries
 * @property float $minDeliveryPrice
 * @property string $minDeliveryPriceFormatted
 * @property int $selectedId
 * @property int $storeSelectedId
 * @property float $distance
 * @property float $duration
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
            'selectedPvz' => $data['selectedPvz'] ?? '',
            'coordinates' => $data['coordinates'] ?? [],
            'completionDate' => $data['completionDate'] ?? '',
            'deliveries' => $data['deliveries'] ?? [],
            'minDeliveryPrice' => $data['minDeliveryPrice'] ?? 0.0,
            'minDeliveryPriceFormatted' => $data['minDeliveryPriceFormatted'] ?? '',
            'selectedId' => $data['selectedId'] ?? 0,
            'storeSelectedId' => $data['storeSelectedId'] ?? 0,
            'distance' => $data['distance'] ?? 0.0,
            'duration' => $data['duration'] ?? 0.0,
        ]);
    }
}
