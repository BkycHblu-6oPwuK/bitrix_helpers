<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Core\Http\Resources\Resource;

/**
 * Адрес (отправитель/получатель/точка «от»/«до»).
 *
 * Поля соответствуют структуре ApiShip Address/FromTo:
 * countryCode, region, city, cityGuid, street, house, block, office,
 * postIndex, lat, lng, addressString.
 *
 * @property string $countryCode
 * @property string|null $region
 * @property string $city
 * @property string|null $cityGuid
 * @property string|null $street
 * @property string|null $house
 * @property string|null $block
 * @property string|null $office
 * @property string|null $postIndex
 * @property float|null $lat
 * @property float|null $lng
 * @property string $addressString
 */
final class AddressDto extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'countryCode'   => (string)($data['countryCode'] ?? 'RU'),
            'region'        => $data['region'] ?? null,
            'city'          => (string)($data['city'] ?? ''),
            'cityGuid'      => $data['cityGuid'] ?? null,
            'street'        => $data['street'] ?? null,
            'house'         => $data['house'] ?? null,
            'block'         => $data['block'] ?? null,
            'office'        => $data['office'] ?? null,
            'postIndex'     => $data['postIndex'] ?? null,
            'lat'           => isset($data['lat']) ? (float)$data['lat'] : null,
            'lng'           => isset($data['lng']) ? (float)$data['lng'] : null,
            'addressString' => (string)($data['addressString'] ?? ''),
        ]);
    }
}
