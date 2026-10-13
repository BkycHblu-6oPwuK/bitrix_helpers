<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

/**
 * Результат маппинга статуса ApiShip → статусы Bitrix.
 *
 * Каждое поле независимо: null означает «не менять эту сущность».
 *  - $orderStatusId    — STATUS_ID заказа (Bitrix\Sale\OrderStatus, тип "O");
 *  - $shipmentStatusId — STATUS_ID отгрузки (Bitrix\Sale\DeliveryStatus, тип "D").
 */
final class StatusMappingDto
{
    public function __construct(
        public readonly ?string $orderStatusId = null,
        public readonly ?string $shipmentStatusId = null,
    ) {}

    /**
     * Нечего применять — оба целевых статуса пусты.
     */
    public function isEmpty(): bool
    {
        return $this->orderStatusId === null && $this->shipmentStatusId === null;
    }
}
