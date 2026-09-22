<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Core\Http\Resources\Resource;

/**
 * Получатель заказа (или отправитель — структура ApiShip идентична).
 *
 * Поля: countryCode, region, city, street, house, block, office, postIndex,
 * contactName, phone, email, comment, addressString, lat, lng.
 *
 * @property-read string $contactName
 * @property-read string $phone
 */
final class RecipientDto extends Resource
{
    public function getContactName(): string
    {
        return (string)$this->getString('contactName');
    }

    public function getPhone(): string
    {
        return (string)$this->getString('phone');
    }

    public function getEmail(): ?string
    {
        return $this->getString('email');
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
     * @return array<string,mixed>
     */
    public function toApiShip(): array
    {
        return array_filter($this->toArray(), static fn($v) => $v !== null && $v !== '');
    }
}
