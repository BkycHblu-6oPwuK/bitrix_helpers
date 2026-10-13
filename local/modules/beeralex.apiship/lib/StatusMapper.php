<?php
declare(strict_types=1);

namespace Beeralex\Apiship;

use Bitrix\Sale\Order;
use Beeralex\Apiship\Dto\StatusMappingDto;
use Beeralex\Apiship\Support\ShipmentLocator;

/**
 * Маппинг статусов ApiShip -> статусы Bitrix + применение к заказу и отгрузке.
 *
 * Один статус ApiShip меняет ОБЕ сущности:
 *  - статус заказа    (Bitrix\Sale\OrderStatus, тип "O") — $order->setField('STATUS_ID', ...);
 *  - статус отгрузки  (Bitrix\Sale\DeliveryStatus, тип "D") — $shipment->setField('STATUS_ID', ...).
 *
 * Карты берутся из настроек модуля (см. Options::$orderStatusMap / $shipmentStatusMap); заданные в
 * админке значения накладываются поверх дефолтных карт из кода. Пустое значение в карте («— не менять —»)
 * означает, что соответствующая сущность не трогается.
 */
final class StatusMapper
{
    public function __construct(
        private readonly Options $options,
    ) {}

    /**
     * Карта берётся из настроек модуля целиком (если задана).
     * После первого сохранения формы настроек карта в опциях содержит все ключи (пустое
     * значение = «не менять»), поэтому переопределяет дефолт полностью, а не по ключу.
     *
     * @return array<string,string>
     */
    protected function getOrderMap(): array
    {
        return $this->options->orderStatusMap !== [] ? $this->options->orderStatusMap : [];
    }

    /**
     * @return array<string,string>
     */
    protected function getShipmentMap(): array
    {
        return $this->options->shipmentStatusMap !== [] ? $this->options->shipmentStatusMap : [];
    }

    /**
     * Возвращает пару целевых статусов Bitrix для ключа статуса ApiShip.
     * Отсутствующее/пустое значение в карте => null (сущность не трогаем).
     */
    public function map(string $apiShipStatusKey): StatusMappingDto
    {
        return new StatusMappingDto(
            self::normalize($this->getOrderMap()[$apiShipStatusKey] ?? null),
            self::normalize($this->getShipmentMap()[$apiShipStatusKey] ?? null),
        );
    }

    private static function normalize(?string $statusId): ?string
    {
        $statusId = $statusId !== null ? trim($statusId) : '';

        return $statusId === '' ? null : $statusId;
    }

    /**
     * Применяет статус ApiShip к заказу Bitrix и его отгрузке ApiShip (если смаппены).
     */
    public function applyToBitrixOrder(int $bitrixOrderId, string $apiShipStatusKey): bool
    {
        $mapping = $this->map($apiShipStatusKey);
        if ($mapping->isEmpty()) {
            return false;
        }

        $order = Order::load($bitrixOrderId);
        if ($order === null) {
            return false;
        }

        $changed = false;

        if ($mapping->orderStatusId !== null
            && $order->getField('STATUS_ID') !== $mapping->orderStatusId
        ) {
            $order->setField('STATUS_ID', $mapping->orderStatusId);
            $changed = true;
        }

        if ($mapping->shipmentStatusId !== null) {
            $shipment = ShipmentLocator::findApiShipShipment($order);
            if ($shipment !== null
                && $shipment->getField('STATUS_ID') !== $mapping->shipmentStatusId
            ) {
                $shipment->setField('STATUS_ID', $mapping->shipmentStatusId);
                $changed = true;
            }
        }

        if (!$changed) {
            return true;
        }

        $result = $order->save();

        return $result->isSuccess();
    }
}
