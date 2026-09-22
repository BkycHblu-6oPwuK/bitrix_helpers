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
 * @property-read string $countryCode
 * @property-read string $city
 * @property-read string $addressString
 */
final class AddressDto extends Resource
{
    public function getCountryCode(): string
    {
        return (string)($this->getString('countryCode') ?? 'RU');
    }

    public function getCity(): string
    {
        return (string)$this->getString('city');
    }

    public function getAddressString(): string
    {
        return (string)$this->getString('addressString');
    }

    /**
     * Преобразует в структуру ApiShip (для From/To/Sender/Recipient).
     *
     * @return array<string,mixed>
     */
    public function toApiShip(): array
    {
        return array_filter($this->toArray(), static fn($v) => $v !== null && $v !== '');
    }
}
