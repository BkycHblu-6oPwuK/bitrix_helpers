<?php

namespace Itb\EventHandlers;

use Bitrix\Main\Event;
use Bitrix\Sale\Order;
use Itb\Checkout\Delivery\ExtraServices\MyPriceExtraService;
use Itb\Enum\OrderStatuses;
use Itb\Restrictions\UserRestriction;

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
        $dir = str_replace($_SERVER['DOCUMENT_ROOT'], '', MyPriceExtraService::getCurDir());
        return new EventResult(
            EventResult::SUCCESS,
            [
                MyPriceExtraService::class => $dir . '/MyPriceExtraService.php'
            ]
        );
    }

    public static function onSalePaySystemRestrictionsClassNamesBuildList()
    {
        $dir = str_replace($_SERVER['DOCUMENT_ROOT'], '', UserRestriction::getCurDir());
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            [
                UserRestriction::class => $dir . '/UserRestriction.php',
            ]
        );
    }

    public static function onSaleCashboxRestrictionsClassNamesBuildList()
    {
        $dir = str_replace($_SERVER['DOCUMENT_ROOT'], '', UserRestriction::getCurDir());
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            [
                UserRestriction::class => $dir . '/UserRestriction.php',
            ]
        );
    }
}
