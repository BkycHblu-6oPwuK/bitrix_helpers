<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Apiship\Enum\WebhookEventType;
use Beeralex\Core\Http\Resources\Resource;

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
 *
 * @property string $eventId         id/uuid события (пусто, если ApiShip не прислал)
 * @property string $type            eventType
 * @property bool $isOrderStatus     ORDER_STATUS (в т.ч. без eventType — по наличию status)
 * @property int $orderId
 * @property string|null $providerNumber
 * @property string|null $clientNumber
 * @property string|null $barcode
 * @property string|null $additionalProviderNumber
 * @property string|null $returnProviderNumber
 * @property string|null $checkUrl
 * @property string|null $trackingUrl
 * @property string|null $providerKey
 * @property string|null $statusKey
 * @property string|null $statusName
 * @property string|null $statusDescription
 * @property string|null $statusErrorCode
 * @property string|null $statusProviderCode
 * @property string|null $statusProviderName
 * @property string|null $statusProviderDescription
 * @property string|null $statusCreated
 * @property string|null $statusCreatedProvider
 * @property bool $hasPlannedDelivery
 * @property string|null $plannedDeliveryDate
 * @property string|null $plannedDeliveryTimeStart
 * @property string|null $plannedDeliveryTimeEnd
 */
final class WebhookEventDto extends Resource
{
    public static function make(array $data): static
    {
        $orderInfo = is_array($data['orderInfo'] ?? null) ? $data['orderInfo'] : [];
        $statusInfo = is_array($data['status'] ?? null) ? $data['status'] : [];
        $plannedDelivery = is_array($orderInfo['plannedDelivery'] ?? null) ? $orderInfo['plannedDelivery'] : [];

        $type = (string)($data['eventType'] ?? '');
        $orderId = $orderInfo['orderId'] ?? null;

        return new static([
            'eventId'                   => (string)($data['id'] ?? $data['uuid'] ?? ''),
            'type'                      => $type,
            'isOrderStatus'             => $type === WebhookEventType::ORDER_STATUS->value
                || ($type === '' && $statusInfo !== []),
            'orderId'                   => is_numeric($orderId) ? (int)$orderId : 0,
            'providerNumber'            => self::str($orderInfo, 'providerNumber'),
            'clientNumber'              => self::str($orderInfo, 'clientNumber'),
            'barcode'                   => self::str($orderInfo, 'barcode'),
            'additionalProviderNumber'  => self::str($orderInfo, 'additionalProviderNumber'),
            'returnProviderNumber'      => self::str($orderInfo, 'returnProviderNumber'),
            'checkUrl'                  => self::str($orderInfo, 'checkUrl'),
            'trackingUrl'               => self::str($orderInfo, 'trackingUrl'),
            'providerKey'               => self::str($orderInfo, 'providerKey'),
            'statusKey'                 => self::str($statusInfo, 'key'),
            'statusName'                => self::str($statusInfo, 'name'),
            'statusDescription'         => self::str($statusInfo, 'description'),
            'statusErrorCode'           => self::str($statusInfo, 'errorCode'),
            'statusProviderCode'        => self::str($statusInfo, 'providerCode'),
            'statusProviderName'        => self::str($statusInfo, 'providerName'),
            'statusProviderDescription' => self::str($statusInfo, 'providerDescription'),
            'statusCreated'             => self::str($statusInfo, 'created'),
            'statusCreatedProvider'     => self::str($statusInfo, 'createdProvider'),
            'hasPlannedDelivery'        => $plannedDelivery !== [],
            'plannedDeliveryDate'       => self::str($plannedDelivery, 'date'),
            'plannedDeliveryTimeStart'  => self::str($plannedDelivery, 'timeStart'),
            'plannedDeliveryTimeEnd'    => self::str($plannedDelivery, 'timeEnd'),
        ]);
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
