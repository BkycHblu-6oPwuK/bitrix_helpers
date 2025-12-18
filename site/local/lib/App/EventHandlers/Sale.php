<?php

namespace App\EventHandlers;

use Bitrix\Main\Event;
use Bitrix\Sale\Order;
use Beeralex\Catalog\Cashbox\CashboxAtolFarm;
use Beeralex\Catalog\Cashbox\PrepaymentCheck;
use Beeralex\Catalog\Enum\OrderStatuses;
use Beeralex\Catalog\ExtraService\MyPriceExtraService;
use Beeralex\Catalog\Restriction\UserRestriction;
use Beeralex\Core\Service\PathService;
use Bitrix\Main\EventResult;

class Sale
{
    /**
     * Событие сохранения заказа
     * Происходит в конце сохранения, когда заказ и все связанные сущности уже сохранены
     * https://dev.1c-bitrix.ru/api_d7/bitrix/sale/events/order_saved.php
     *
     * @param Event $event
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\NotImplementedException
     */
    public static function onSaleOrderSaved(Event $event)
    {
        /** @var Order $order */
        $order = $event->getParameter('ENTITY');
    }

    public static function OnSaleOrderBeforeSaved(Event $event)
    {
        /** @var Order $order */
        $order = $event->getParameter('ENTITY');
    }

    public static function onSaleDeliveryExtraServicesClassNamesBuildList()
    {
        $pathService = \service(PathService::class);
        $filepath = str_replace($_SERVER['DOCUMENT_ROOT'], '', $pathService->classFile(MyPriceExtraService::class));
        return new EventResult(
            EventResult::SUCCESS,
            [
                MyPriceExtraService::class => $filepath
            ]
        );
    }
}
