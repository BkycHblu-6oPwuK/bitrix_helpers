<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Sdk;

use Apiship\Apiship;
use Beeralex\Apiship\Sdk\Api\Connections;
use Beeralex\Apiship\Sdk\Api\ExternalTracking;
use Beeralex\Apiship\Sdk\Api\Lists as ExtendedLists;
use Beeralex\Apiship\Sdk\Api\Orders as ExtendedOrders;
use Beeralex\Apiship\Sdk\Api\Webhooks;

/**
 * Фасад над официальным SDK ApiShip, добавляющий непокрытые группы методов.
 *
 * Наследует штатные фабрики orders()/calculator()/lists()/courierCall() и добавляет
 * connections()/webhooks()/externalTracking(), а также расширенные Orders/Lists.
 *
 * Расширение живёт внутри модуля (lib/Sdk), vendor не изменяется — см. docs/DECISIONS.md ADR-001.
 */
class ExtendedApiship extends Apiship
{
    /** Подключения к службам доставки (CRUD). */
    public function connections(): Connections
    {
        return new Connections($this->adapter);
    }

    /** Подписки на вебхуки. */
    public function webhooks(): Webhooks
    {
        return new Webhooks($this->adapter);
    }

    /** Трекинг сторонних заказов. */
    public function externalTracking(): ExternalTracking
    {
        return new ExternalTracking($this->adapter);
    }

    /** Расширенные методы справочников (statuses, tariffs). */
    public function listsExtended(): ExtendedLists
    {
        return new ExtendedLists($this->adapter);
    }

    /** Расширенные методы заказов (список, labels). */
    public function ordersExtended(): ExtendedOrders
    {
        return new ExtendedOrders($this->adapter);
    }
}
