<?php
declare(strict_types=1);

namespace Beeralex\Apiship;

use Beeralex\Apiship\Contracts\OrderServiceContract;
use Beeralex\Apiship\Support\ShipmentLocator;
use Bitrix\Main\Application;
use Bitrix\Main\Event;
use Bitrix\Sale\Order;

class EventHandlers
{
    /** @var array<int,true> Заказы, для которых отправка уже запланирована в этом хите. */
    protected static array $scheduled = [];

    /**
     * Заказ сохранён: если он сейчас в статусе «отправлять в ApiShip» и у него доставка ApiShip,
     * планируем создание заказа в ApiShip.
     *
     * Почему OnSaleOrderSaved, а не OnSaleStatusOrderChange: тот срабатывает только в момент смены
     * статуса и внутри ещё не завершённого сохранения. Если нужный статус выставляется заказу сразу
     * при создании (или был выставлен раньше, а отправка не удалась), событие смены не придёт,
     * а у нового заказа ID может быть ещё не известен. Повторные вызовы безопасны:
     * OrderService::createForBitrixOrder() идемпотентен.
     */
    public static function onSaleOrderSaved(Event $event): void
    {
        $options = \service(Options::class);
        if ($options->sendOnStatusId === '') {
            return;
        }

        $order = $event->getParameter('ENTITY');
        if (!$order instanceof Order) {
            return;
        }

        $orderId = (int)$order->getId();
        if ($orderId <= 0 || isset(static::$scheduled[$orderId])) {
            return;
        }

        if ((string)$order->getField('STATUS_ID') !== $options->sendOnStatusId) {
            return;
        }

        // заказы без доставки ApiShip — это норма, не ошибка
        if (ShipmentLocator::findApiShipShipment($order) === null) {
            return;
        }

        static::$scheduled[$orderId] = true;
        static::runAfterSave($orderId);
    }

    /**
     * Устаревший обработчик OnSaleStatusOrderChange. Оставлен пустым, чтобы уже зарегистрированное
     * в БД событие не падало до переустановки обработчиков; отправку делает onSaleOrderSaved().
     *
     * @deprecated
     */
    public static function onSaleStatusOrderChange(Event $event): void
    {
    }

    /**
     * Создание заказа — HTTP-запрос к ApiShip и повторное сохранение заказа (TRACKING_NUMBER).
     * Делаем это не внутри сохранения заказа, а после его завершения: в фоновой задаче
     * (после отправки ответа клиенту), а если фоновых задач нет — сразу.
     */
    protected static function runAfterSave(int $orderId): void
    {
        $app = Application::getInstance();

        if (method_exists($app, 'addBackgroundJob')) {
            $app->addBackgroundJob([static::class, 'send'], [$orderId]);
            return;
        }

        static::send($orderId);
    }

    public static function send(int $orderId): void
    {
        try {
            \service(OrderServiceContract::class)->createForBitrixOrder($orderId);
        } catch (\Throwable $e) {
            \AddMessage2Log("ApiShip: не удалось отправить заказ #{$orderId}: {$e->getMessage()}", 'beeralex.apiship');
        }
    }
}
