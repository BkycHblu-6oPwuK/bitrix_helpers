<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Apiship\Support\Concerns\Arrayable;
use Beeralex\Apiship\Support\Concerns\NormalizesValues;

/**
 * Адрес (отправитель/получатель/точка «от»/«до»).
 *
 * Поля соответствуют структуре ApiShip Address/FromTo:
 * countryCode, region, city, cityGuid, street, house, block, office,
 * postIndex, lat, lng, addressString.
 */
class AddressDto
{
    use Arrayable;
    use NormalizesValues;

    public function __construct(
        public readonly string $countryCode,
        public readonly ?string $region,
        public readonly string $city,
        public readonly ?string $cityGuid,
        public readonly ?string $street,
        public readonly ?string $house,
        public readonly ?string $block,
        public readonly ?string $office,
        public readonly ?string $postIndex,
        public readonly ?float $lat,
        public readonly ?float $lng,
        public readonly string $addressString,
    ) {}

    public static function make(array $data): static
    {
        return new static(
            countryCode: (string)($data['countryCode'] ?? 'RU'),
            region: static::nullableString($data['region'] ?? null),
            city: (string)($data['city'] ?? ''),
            cityGuid: static::nullableString($data['cityGuid'] ?? null),
            street: static::nullableString($data['street'] ?? null),
            house: static::nullableString($data['house'] ?? null),
            block: static::nullableString($data['block'] ?? null),
            office: static::nullableString($data['office'] ?? null),
            postIndex: static::nullableString($data['postIndex'] ?? null),
            lat: isset($data['lat']) ? (float)$data['lat'] : null,
            lng: isset($data['lng']) ? (float)$data['lng'] : null,
            addressString: (string)($data['addressString'] ?? ''),
        );
    }
}
