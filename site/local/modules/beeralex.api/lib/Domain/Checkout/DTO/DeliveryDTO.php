<?php

namespace Beeralex\Api\Domain\Checkout\DTO;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $ownName
 * @property string $description
 * @property string $currency
 * @property int $sort
 * @property ExtraServiceDTO[] $extraServices
 * @property bool $isStoreDelivery
 * @property string $logotip
 * @property bool $isSelected
 * @property StoreDTO[] $storeList
 * @property float $price
 * @property string $priceFormatted
 * @property string $deliveryPeriod
 * 
 * DTO способа доставки
 */
class DeliveryDTO extends Resource
{
    public static function make(array $data): static
    {
        $extraServices = array_map([ExtraServiceDTO::class, 'make'], $data['extraServices'] ?? []);
        $storeList = array_map([StoreDTO::class, 'make'], $data['storeList'] ?? []);

        return new static([
            'id' => $data['id'] ?? 0,
            'code' => $data['code'] ?? '',
            'name' => $data['name'] ?? '',
            'ownName' => $data['ownName'] ?? '',
            'description' => $data['description'] ?? '',
            'currency' => $data['currency'] ?? '',
            'sort' => $data['sort'] ?? 0,
            'extraServices' => $extraServices,
            'isStoreDelivery' => $data['isStoreDelivery'] ?? false,
            'logotip' => $data['logotip'] ?? '',
            'isSelected' => $data['isSelected'] ?? false,
            'storeList' => $storeList,
            'price' => $data['price'] ?? 0.0,
            'priceFormatted' => $data['priceFormatted'] ?? '',
            'deliveryPeriod' => $data['deliveryPeriod'] ?? '',
        ]);
    }
}
