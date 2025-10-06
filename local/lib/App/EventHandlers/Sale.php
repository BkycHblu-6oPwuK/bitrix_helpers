<?php

namespace App\EventHandlers;

use Bitrix\Main\Event;
use Bitrix\Sale\Order;
use App\Catalog\Cashbox\CashboxAtolFarm;
use App\Catalog\Cashbox\PrepaymentCheck;
use App\Catalog\ExtraServices\MyPriceExtraService;
use App\Catalog\Enum\OrderStatuses;
use App\Restriction\UserRestriction;
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
        if ($order->getField("STATUS_ID") == OrderStatuses::CANCELED->value) {
            $order->setField("CANCELED", "Y");
        }
    }

    public static function onSaleDeliveryExtraServicesClassNamesBuildList()
    {
        $filepath = str_replace($_SERVER['DOCUMENT_ROOT'], '', \Beeralex\Core\Helpers\PathHelper::classFile(MyPriceExtraService::class));
        return new EventResult(
            EventResult::SUCCESS,
            [
                MyPriceExtraService::class => $filepath
            ]
        );
    }

    public static function onSalePaySystemRestrictionsClassNamesBuildList()
    {
        $filepath = str_replace($_SERVER['DOCUMENT_ROOT'], '', \Beeralex\Core\Helpers\PathHelper::classFile(UserRestriction::class));
        return new EventResult(
            EventResult::SUCCESS,
            [
                UserRestriction::class => $filepath,
            ]
        );
    }

    public static function onSaleCashboxRestrictionsClassNamesBuildList()
    {
        $filepath = str_replace($_SERVER['DOCUMENT_ROOT'], '', \Beeralex\Core\Helpers\PathHelper::classFile(UserRestriction::class));
        return new EventResult(
            EventResult::SUCCESS,
            [
                UserRestriction::class => $filepath,
            ]
        );
    }

    /*
    public static function onGetCustomCashboxHandlers()
    {
        $filepath = str_replace($_SERVER['DOCUMENT_ROOT'], '', \Beeralex\Core\Helpers\PathHelper::classFile(YourCashboxClass::class);
        return new EventResult(
            EventResult::SUCCESS,
            [
                CashboxAtolFarm::class => $filepath,
            ]
        );
    }*/

    public static function onGetCustomCheckList()
    {
        $filepath = str_replace($_SERVER['DOCUMENT_ROOT'], '', \Beeralex\Core\Helpers\PathHelper::classFile(PrepaymentCheck::class));
        return new EventResult(
            EventResult::SUCCESS,
            [
                PrepaymentCheck::class => $filepath,
            ]
        );
    }
}
