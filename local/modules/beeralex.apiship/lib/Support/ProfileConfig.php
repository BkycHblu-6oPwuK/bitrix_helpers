<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Support;

use Bitrix\Sale\Delivery\Services\Manager;
use Bitrix\Sale\Shipment;

final class ProfileConfig
{
    /** @param int[] $tariffIds */
    public function __construct(
        public readonly string $connectId,
        public readonly string $providerKey,
        public readonly array $tariffIds,
        public readonly ?int $deliveryType,
        public readonly ?int $pickupType,
        public readonly int $pointInId,
    ) {}

    /** @param array<string,mixed> $main секция MAIN конфига */
    public static function fromArray(array $main): self
    {
        $key = (string)($main['CONNECTION_KEY'] ?? '');
        if ($key !== '' && str_contains($key, '|')) {
            [$connectId, $providerKey] = explode('|', $key, 2);
        } else { // старый формат
            $connectId = (string)($main['PROVIDER_CONNECT_ID'] ?? '');
            $providerKey = (string)($main['PROVIDER_KEY'] ?? '');
        }

        $tariffIds = [];
        foreach ((array)($main['TARIFF_ID'] ?? []) as $value) {
            $id = (int)$value;
            if ($id > 0) {
                $tariffIds[$id] = $id;
            }
        }

        return new self(
            $connectId,
            $providerKey,
            array_values($tariffIds),
            (int)($main['DELIVERY_TYPE'] ?? 0) ?: null,
            (int)($main['PICKUP_TYPE'] ?? 0) ?: null,
            (int)($main['POINT_IN_ID'] ?? 0),
        );
    }

    public static function fromShipment(Shipment $shipment): self
    {
        $delivery = Manager::getObjectById($shipment->getDeliveryId());
        $config = $delivery ? $delivery->getConfigValues() : [];

        return self::fromArray((array)($config['MAIN'] ?? $config));
    }

    /** Тип забора: заданный в профиле, иначе по наличию склада СД. */
    public function resolvedPickupType(): int
    {
        return $this->pickupType ?: ($this->pointInId > 0 ? 2 : 1);
    }
}