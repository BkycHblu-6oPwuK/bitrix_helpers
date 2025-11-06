<?php
namespace Beeralex\Catalog\Checkout;

use Bitrix\Sale\Order;
use Beeralex\Catalog\Helper\PriceHelper;

class TotalBuilder
{
    /**
     * мутирует $basketSummary полученную из BasketFacade под заказ
     */
    public function build(Order $order, array &$basketSummary): void
    {
        $basketSummary['deliveryPrice'] = $order->getDeliveryPrice();
        $basketSummary['deliveryPriceFormatted'] = PriceHelper::format($basketSummary['deliveryPrice']);
        $basketSummary['totalItemsPrice'] = $basketSummary['totalPrice'];
        $basketSummary['totalItemsPriceFormatted'] = $basketSummary['totalPriceFormatted'];
        $basketSummary['totalPrice'] = $basketSummary['deliveryPrice'] + $basketSummary['totalItemsPrice'];
        $basketSummary['totalPriceFormatted'] = PriceHelper::format($basketSummary['totalPrice']);
    }
}
