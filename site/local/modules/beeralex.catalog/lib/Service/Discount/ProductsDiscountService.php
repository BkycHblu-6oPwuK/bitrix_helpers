<?php
declare(strict_types=1);
namespace Beeralex\Catalog\Service\Discount;

use Bitrix\Sale\BasketBase;

class ProductsDiscountService extends DiscountService
{
    public function __construct(
        BasketBase $basket, 
        protected array $basketCodes = [])
    {
        parent::__construct($basket);
    }

    public function getPrices(): array
    {
        $prices = [];
        foreach ($this->basketCodes as $productId => $code) {
            $prices[$productId] = parent::getPrice($code);
        }
        return $prices;
    }

    public function getPriceByProductId(int $productId): ?float
    {
        return $this->getPrice($this->basketCodes[$productId] ?? 0);
    }
}
