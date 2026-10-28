<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Sdk;

use Apiship\Apiship;
use Beeralex\Apiship\Sdk\Api\Billing;
use Beeralex\Apiship\Sdk\Api\Calculator as ExtendedCalculator;
use Beeralex\Apiship\Sdk\Api\Connections;
use Beeralex\Apiship\Sdk\Api\ExternalTracking;
use Beeralex\Apiship\Sdk\Api\Lists as ExtendedLists;
use Beeralex\Apiship\Sdk\Api\Orders as ExtendedOrders;
use Beeralex\Apiship\Sdk\Api\ProviderSpecific;
use Beeralex\Apiship\Sdk\Api\Webhooks;

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

    /** Расширенные методы справочников (statuses, tariffs, providerCities и др.). */
    public function listsExtended(): ExtendedLists
    {
        return new ExtendedLists($this->adapter);
    }

    /** Расширенные методы заказов (validate, return, labels, items и др.). */
    public function ordersExtended(): ExtendedOrders
    {
        return new ExtendedOrders($this->adapter);
    }

    /** Методы служб доставки (dpd/logsis/x5/yataxi/ozondel/boxberry/cse). */
    public function providerSpecific(): ProviderSpecific
    {
        return new ProviderSpecific($this->adapter);
    }

    /** Биллинг и онлайн-чеки. */
    public function billing(): Billing
    {
        return new Billing($this->adapter);
    }

    /** Интервалы доставки (calculator/intervals). */
    public function calculatorExtended(): ExtendedCalculator
    {
        return new ExtendedCalculator($this->adapter);
    }
}
