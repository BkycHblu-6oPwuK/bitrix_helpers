<?php
declare(strict_types=1);

namespace Beeralex\Apiship;

use Bitrix\Main\Event;
use Bitrix\Sale\Order;
use Beeralex\Apiship\Contracts\OrderServiceContract;
use Beeralex\Apiship\Exception\ApiShipException;

/**
 * Обработчики событий модуля sale.
 *
 * onSaleStatusOrderChange реализует ADR-005: заказ отправляется в ApiShip при переходе
 * в статус, заданный в настройках модуля (Options::sendOnStatusId). Если настройка пуста —
 * автоотправка отключена (создание заказа доступно только через OrderServiceContract явно).
 */
final class EventHandlers
{
    public static function onSaleStatusOrderChange(Event $event): void
    {
        $options = \service(Options::class);
        if ($options->sendOnStatusId === '') {
            return;
        }

        $parameters = $event->getParameters();
        /** @var Order|null $order */
        $order = $parameters['ENTITY'] ?? null;
        $newStatus = $parameters['VALUE'] ?? null;

        if (!$order instanceof Order || $newStatus !== $options->sendOnStatusId) {
            return;
        }

        try {
            \service(OrderServiceContract::class)->createForBitrixOrder($order->getId());
        } catch (ApiShipException $e) {
            if ($options->logsEnable) {
                \AddMessage2Log(
                    "ApiShip: не удалось отправить заказ #{$order->getId()}: {$e->getMessage()}",
                    'beeralex.apiship'
                );
            }
        }
    }
}
