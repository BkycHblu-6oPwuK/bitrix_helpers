<?php
declare(strict_types=1);

namespace Beeralex\Apiship;

use Bitrix\Main\Event;
use Bitrix\Sale\Order;
use Beeralex\Apiship\Contracts\OrderServiceContract;
use Beeralex\Apiship\Support\ShipmentLocator;

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

        // Пропускаем заказы без доставки ApiShip — это норма, не ошибка
        if (ShipmentLocator::findApiShipShipment($order) === null) {
            return;
        }

        try {
            \service(OrderServiceContract::class)->createForBitrixOrder($order->getId());
        } catch (\Throwable $e) {
            \AddMessage2Log(
                "ApiShip: не удалось отправить заказ #{$order->getId()}: {$e->getMessage()}",
                'beeralex.apiship'
            );
        }
    }
}
