<?php
namespace Beeralex\Api\Domain\Checkout;

use Bitrix\Sale\Order;
use Beeralex\Api\Domain\Checkout\DTO\TotalDTO;
use Beeralex\Catalog\Service\PriceService;
use \Beeralex\Api\Domain\Checkout\DTO\ServiceDTO;

class TotalBuilder
{
    private PriceService $priceService;

    public function __construct()
    {
        $this->priceService = \service(PriceService::class);
    }

    /**
     * Создаёт TotalDTO на основе basketSummary и цены доставки
     */
    public function build(Order $order, array $basketSummary): TotalDTO
    {
        $deliveryPrice = $order->getDeliveryPrice();
        $basketPrice = (float)($basketSummary['TOTAL_PRICE'] ?? 0);
        $basketPriceWithoutDiscount = (float)($basketSummary['TOTAL_PRICE_WITHOUT_DISCOUNT'] ?? 0);
        $servicesPrice = (float)($basketSummary['SERVICES_TOTAL_PRICE'] ?? 0);
        $discount = (float)($basketSummary['TOTAL_DISCOUNT'] ?? 0);
        $totalPrice = $basketPrice + $servicesPrice + $deliveryPrice;
        $services = $basketSummary['SERVICES'] ?? [];

        return TotalDTO::make([
            'basket' => $basketPrice,
            'basketFormatted' => $basketSummary['TOTAL_PRICE_FORMATTED'] ?? $this->priceService->format($basketPrice),
            'basketWithoutDiscount' => $basketPriceWithoutDiscount,
            'basketWithoutDiscountFormatted' => $basketSummary['TOTAL_PRICE_WITHOUT_DISCOUNT_FORMATTED'] ?? $this->priceService->format($basketPriceWithoutDiscount),
            'delivery' => $deliveryPrice,
            'deliveryFormatted' => $this->priceService->format($deliveryPrice),
            'discount' => $discount,
            'discountFormatted' => $this->priceService->format($discount),
            'total' => $totalPrice,
            'totalFormatted' => $this->priceService->format($totalPrice),
            'quantity' => (int)($basketSummary['TOTAL_QUANTITY'] ?? 0),
            'positionQuantity' => (int)($basketSummary['TOTAL_POSITION_QUANTITY'] ?? 0),
            'services' => array_map(fn($service) => ServiceDTO::make($service), $services),
        ]);
    }
}
