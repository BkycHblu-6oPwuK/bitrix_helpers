<?php

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Sale\Order;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class ItbDressing extends \CBitrixComponent
{
    public function executeComponent()
    {   
        if(!Loader::includeModule('itb.dressing')) return;
        $this->arResult['order'] = $this->getOrder();
        $this->includeComponentTemplate();
    }

    public function getOrder() : ?Order
    {
        $request = Context::getCurrent()->getRequest();
        $orderId = $request->get('ORDER_ID');
        $order = null;
        if($orderId && $orderId = (int)$orderId){
            $order = Order::load($orderId);
        }
        return $order;
    }
}
