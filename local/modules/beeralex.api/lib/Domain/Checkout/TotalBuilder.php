<?php
namespace Beeralex\Api\Domain\Checkout;

use Bitrix\Sale\Order;
use Beeralex\Catalog\Helper\PriceHelper;
use Beeralex\Api\Domain\Checkout\DTO\TotalPriceDTO;
use Beeralex\Catalog\Service\PriceService;

class TotalBuilder
{
    private PriceService $priceService;

    public function __construct()
    {
        $this->priceService = \service(PriceService::class);
    }

    /**
     * Создаёт TotalPriceDTO на основе basketSummary и цены доставки
     */
    public function build(Order $order, array $basketSummary): TotalPriceDTO
    {
        $deliveryPrice = $order->getDeliveryPrice();
        $basketPrice = (float)($basketSummary['totalPrice'] ?? 0);
        $discount = (float)($basketSummary['totalDiscount'] ?? 0);
        $totalPrice = $basketPrice + $deliveryPrice;

        return TotalPriceDTO::make([
            'basket' => $basketPrice,
            'basketFormatted' => $basketSummary['totalPriceFormatted'] ?? $this->priceService->format($basketPrice),
            'delivery' => $deliveryPrice,
            'deliveryFormatted' => $this->priceService->format($deliveryPrice),
            'discount' => $discount,
            'discountFormatted' => $basketSummary['totalDiscountFormatted'] ?? $this->priceService->format($discount),
            'total' => $totalPrice,
            'totalFormatted' => $this->priceService->format($totalPrice),
        ]);
    }
}
