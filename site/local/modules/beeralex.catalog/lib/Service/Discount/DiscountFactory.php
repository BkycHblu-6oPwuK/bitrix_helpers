<?php
declare(strict_types=1);
namespace Beeralex\Catalog\Service\Discount;

use Beeralex\Catalog\Repository\PriceRepository;
use Beeralex\Catalog\Service\PriceService;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketBase;

class DiscountFactory
{
    public function __construct(
        protected readonly string $siteId,
        protected readonly PriceService $priceService, 
        protected readonly PriceRepository $priceRepository
    ) {}

    public function createDiscount(BasketBase $basket): DiscountService
    {
        return new DiscountService($basket);
    }

    public function createProductsDiscount(array $productsIds, array $catalogTypePrices): ProductsDiscountService
    {
        [$basket, $basketCodes] = $this->makeProductsBasket($productsIds, $catalogTypePrices);
        return new ProductsDiscountService($basket, $basketCodes);
    }

    protected function makeProductsBasket(array $productsIds, array $catalogTypePrices): array
    {
        $basket = Basket::create($this->siteId);

        $rows = [];
        $dbPrice = $this->priceRepository->getList([
            'select' => ['PRICE', 'CURRENCY', 'PRODUCT_ID', 'CATALOG_GROUP_ID', 'ID'],
            'filter' => ['@PRODUCT_ID' => $productsIds, '@CATALOG_GROUP_ID' => $catalogTypePrices]
        ]);
        $baseCurrency = $this->priceService->getBaseCurrency();
        while ($priceRow = $dbPrice->fetch()) {
            if ($baseCurrency != $priceRow['CURRENCY']) {
                $priceRow['PRICE'] = \CCurrencyRates::ConvertCurrency(
                    $priceRow['PRICE'],
                    $priceRow['CURRENCY'],
                    $baseCurrency
                );
                $priceRow['CURRENCY'] = $baseCurrency;
            }
            $rows[(int)$priceRow['PRODUCT_ID']] = $priceRow;
        }

        $basketCodes = [];
        foreach ($productsIds as $productId) {
            try {
                $priceRow = $rows[$productId] ?? null;
                if (empty($priceRow)) continue;
                $item = $basket->createItem('catalog', $productId);
                $item->setFieldsNoDemand([
                    'PRODUCT_ID' => $productId,
                    'QUANTITY' => 1,
                    'PRICE' => $priceRow['PRICE'],
                    'BASE_PRICE' => $priceRow['PRICE'],
                    'CURRENCY' => $priceRow['CURRENCY'],
                    'PRODUCT_PRICE_ID' => $priceRow['ID'] ?? null,
                    'LID' => $this->siteId,
                    'CAN_BUY' => 'Y',
                    'DELAY' => 'N',
                    'PRICE_TYPE_ID' => $priceRow['CATALOG_GROUP_ID'],
                ]);
                $basketCodes[$productId] = $item->getBasketCode();
            } catch (\Exception $e) {
            }
        }

        return [$basket, $basketCodes];
    }
}
