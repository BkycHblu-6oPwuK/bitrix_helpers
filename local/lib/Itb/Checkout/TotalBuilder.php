<?php

namespace Itb\Checkout;

use Bitrix\Sale\Order;
use Itb\Catalog\Price;

class TotalBuilder
{
    /**
     * мутирует $basketSummary полученную из BasketFacade под заказ
     */
    public function build(Order $order, array &$basketSummary): void
    {
        $basketSummary['deliveryPrice'] = $order->getDeliveryPrice();
        $basketSummary['deliveryPriceFormatted'] = Price::format($basketSummary['deliveryPrice']);
        $basketSummary['totalItemsPrice'] = $basketSummary['totalPrice'];
        $basketSummary['totalItemsPriceFormatted'] = $basketSummary['totalPriceFormatted'];
        $basketSummary['totalPrice'] = $basketSummary['deliveryPrice'] + $basketSummary['totalItemsPrice'];
        $basketSummary['totalPriceFormatted'] = Price::format($basketSummary['totalPrice']);
    }
}
