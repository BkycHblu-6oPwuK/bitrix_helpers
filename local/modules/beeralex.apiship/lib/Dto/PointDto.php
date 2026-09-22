<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Core\Http\Resources\Resource;

/**
 * Пункт выдачи/приёма заказов (ПВЗ).
 *
 * @property-read string $id
 * @property-read string $providerKey
 * @property-read string $name
 * @property-read string $address
 * @property-read float $lat
 * @property-read float $lng
 */
final class PointDto extends Resource
{
    public function getId(): string
    {
        return (string)$this->get('id');
    }

    public function getProviderKey(): string
    {
        return (string)$this->getString('providerKey');
    }

    public function getName(): string
    {
        return (string)$this->getString('name');
    }

    public function getAddress(): string
    {
        return (string)$this->getString('address');
    }

    public function getLat(): ?float
    {
        return $this->getFloat('lat');
    }

    public function getLng(): ?float
    {
        return $this->getFloat('lng');
    }
}
