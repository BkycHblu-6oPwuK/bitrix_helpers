<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Enum;

/**
 * Типы входящих вебхуков ApiShip.
 */
enum WebhookEventType: string
{
    case ORDER_STATUS = 'ORDER_STATUS';
    case PLANNED_DELIVERY_DATE_UPDATE = 'PLANNED_DELIVERY_DATE_UPDATE';
    case ORDER_NUMBER_UPDATE = 'ORDER_NUMBER_UPDATE';
}
