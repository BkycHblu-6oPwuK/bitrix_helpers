<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Apiship\Support\Concerns\Arrayable;
use Beeralex\Apiship\Enum\WebhookEventType;

/**
 * Разобранное событие входящего вебхука ApiShip.
 *
 * ApiShip присылает 3 типа событий со вложенной структурой:
 *  - ORDER_STATUS                 — orderInfo{...} + status{...} (в примерах без eventType);
 *  - PLANNED_DELIVERY_DATE_UPDATE — orderInfo{... plannedDelivery{...}};
 *  - ORDER_NUMBER_UPDATE          — orderInfo{orderId, providerNumber, clientNumber}.
 *
 * plannedDelivery (если у заказа есть плановая дата) всегда приходит ВНУТРИ orderInfo.
 * make() разворачивает вложенную структуру (orderInfo/status/plannedDelivery) в плоские свойства.
 */
class WebhookEventDto
{
    use Arrayable;

    public function __construct(
        /** id/uuid события (пусто, если ApiShip не прислал) */
        public readonly string $eventId,
        /** eventType */
        public readonly string $type,
        /** ORDER_STATUS (в т.ч. без eventType — по наличию status) */
        public readonly bool $isOrderStatus,
        public readonly int $orderId,
        public readonly ?string $providerNumber,
        public readonly ?string $clientNumber,
        public readonly ?string $barcode,
        public readonly ?string $additionalProviderNumber,
        public readonly ?string $returnProviderNumber,
        public readonly ?string $checkUrl,
        public readonly ?string $trackingUrl,
        public readonly ?string $providerKey,
        public readonly ?string $statusKey,
        public readonly ?string $statusName,
        public readonly ?string $statusDescription,
        public readonly ?string $statusErrorCode,
        public readonly ?string $statusProviderCode,
        public readonly ?string $statusProviderName,
        public readonly ?string $statusProviderDescription,
        public readonly ?string $statusCreated,
        public readonly ?string $statusCreatedProvider,
        public readonly bool $hasPlannedDelivery,
        public readonly ?string $plannedDeliveryDate,
        public readonly ?string $plannedDeliveryTimeStart,
        public readonly ?string $plannedDeliveryTimeEnd,
    ) {}

    public static function make(array $data): static
    {
        $orderInfo = is_array($data['orderInfo'] ?? null) ? $data['orderInfo'] : [];
        $statusInfo = is_array($data['status'] ?? null) ? $data['status'] : [];
        $plannedDelivery = is_array($orderInfo['plannedDelivery'] ?? null) ? $orderInfo['plannedDelivery'] : [];

        $type = (string)($data['eventType'] ?? '');
        $orderId = $orderInfo['orderId'] ?? null;

        return new static(
            eventId: (string)($data['id'] ?? $data['uuid'] ?? ''),
            type: $type,
            isOrderStatus: $type === WebhookEventType::ORDER_STATUS->value
                || ($type === '' && $statusInfo !== []),
            orderId: is_numeric($orderId) ? (int)$orderId : 0,
            providerNumber: static::str($orderInfo, 'providerNumber'),
            clientNumber: static::str($orderInfo, 'clientNumber'),
            barcode: static::str($orderInfo, 'barcode'),
            additionalProviderNumber: static::str($orderInfo, 'additionalProviderNumber'),
            returnProviderNumber: static::str($orderInfo, 'returnProviderNumber'),
            checkUrl: static::str($orderInfo, 'checkUrl'),
            trackingUrl: static::str($orderInfo, 'trackingUrl'),
            providerKey: static::str($orderInfo, 'providerKey'),
            statusKey: static::str($statusInfo, 'key'),
            statusName: static::str($statusInfo, 'name'),
            statusDescription: static::str($statusInfo, 'description'),
            statusErrorCode: static::str($statusInfo, 'errorCode'),
            statusProviderCode: static::str($statusInfo, 'providerCode'),
            statusProviderName: static::str($statusInfo, 'providerName'),
            statusProviderDescription: static::str($statusInfo, 'providerDescription'),
            statusCreated: static::str($statusInfo, 'created'),
            statusCreatedProvider: static::str($statusInfo, 'createdProvider'),
            hasPlannedDelivery: $plannedDelivery !== [],
            plannedDeliveryDate: static::str($plannedDelivery, 'date'),
            plannedDeliveryTimeStart: static::str($plannedDelivery, 'timeStart'),
            plannedDeliveryTimeEnd: static::str($plannedDelivery, 'timeEnd'),
        );
    }

    /**
     * Достаёт строковое значение по ключу из вложенного массива; пусто/не-скаляр -> null.
     *
     * @param array<string,mixed> $data
     */
    private static function str(array $data, string $key): ?string
    {
        $value = $data[$key] ?? null;
        if ($value === null || $value === '') {
            return null;
        }

        return is_scalar($value) ? (string)$value : null;
    }
}
