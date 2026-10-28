<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Enum;

/** Типы входящих вебхуков ApiShip (openapi: /webhooks). */
enum WebhookEventType: string
{
    case ORDER_STATUS        = 'ORDER_STATUS';
    case ORDER_NUMBER_UPDATE = 'ORDER_NUMBER_UPDATE';
}
