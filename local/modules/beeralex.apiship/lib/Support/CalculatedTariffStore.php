<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Support;

use Beeralex\Apiship\Dto\TariffDto;
use Beeralex\Apiship\Options;
use Bitrix\Sale\Order;
use Bitrix\Sale\PropertyValue;

/**
 * Свойство заказа хранит JSON-карту {"<deliveryId>": {"tariffId": 19, "deliveryType": 1}}:
 * профилей ApiShip в оформлении несколько, и расчёт каждого не должен затирать остальные.
 */
final class CalculatedTariffStore
{
    public function __construct(private readonly Options $options) {}

    public function save(Order $order, int $deliveryId, TariffDto $tariff): void
    {
        $property = $this->property($order);
        if ($property === null) {
            return;
        }

        $map = $this->decode($property->getValue());
        $map[$deliveryId] = ['tariffId' => $tariff->tariffId, 'deliveryType' => $tariff->deliveryType];
        $property->setValue(json_encode($map, JSON_UNESCAPED_UNICODE));
    }

    /** @return array{tariffId:int,deliveryType:int}|null */
    public function find(Order $order, int $deliveryId): ?array
    {
        $row = $this->decode($this->property($order)?->getValue())[$deliveryId] ?? null;
        $tariffId = is_array($row) ? (int)($row['tariffId'] ?? 0) : 0;

        return $tariffId > 0 ? ['tariffId' => $tariffId, 'deliveryType' => (int)($row['deliveryType'] ?? 0)] : null;
    }

    private function property(Order $order): ?PropertyValue
    {
        return $order->getPropertyCollection()->getItemByOrderPropertyCode($this->options->propCalculatedTariff);
    }

    /** @return array<int,mixed> */
    private function decode(mixed $raw): array
    {
        $data = is_string($raw) && $raw !== '' ? json_decode($raw, true) : null;
        return is_array($data) ? $data : [];
    }
}
