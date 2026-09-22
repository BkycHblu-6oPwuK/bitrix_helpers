<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Core\Http\Resources\Resource;

/**
 * Входные данные для создания заказа ApiShip.
 *
 * Собирается ApiShipOrderRequestBuilder из Bitrix Order/Shipment.
 *
 * @property-read int $bitrixOrderId
 * @property-read string $providerKey
 * @property-read int $tariffId
 * @property-read int $providerConnectId
 */
final class CreateOrderDto extends Resource
{
    public function getBitrixOrderId(): int
    {
        return (int)$this->getInt('bitrixOrderId');
    }

    public function getProviderKey(): string
    {
        return (string)$this->getString('providerKey');
    }

    public function getTariffId(): ?int
    {
        return $this->getInt('tariffId');
    }

    public function getProviderConnectId(): ?int
    {
        return $this->getInt('providerConnectId');
    }

    public function getClientNumber(): string
    {
        return (string)$this->getString('clientNumber');
    }

    public function getSender(): array
    {
        return (array)($this->get('sender') ?? []);
    }

    public function getRecipient(): array
    {
        return (array)($this->get('recipient') ?? []);
    }

    public function getCost(): array
    {
        return (array)($this->get('cost') ?? []);
    }

    /** @return array<int,array<string,mixed>> */
    public function getItems(): array
    {
        return (array)($this->get('items') ?? []);
    }

    /** @return array<int,array<string,mixed>> */
    public function getPlaces(): array
    {
        return (array)($this->get('places') ?? []);
    }
}
