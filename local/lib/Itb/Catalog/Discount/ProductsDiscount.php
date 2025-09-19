<?php

namespace Itb\Catalog\Discount;

use Bitrix\Main\Loader;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketBase;

class ProductsDiscount extends Discount
{
    protected array $productsIds = [];
    protected array $catalogTypePrices = [];
    protected array $basketCodes = [];

    /**
     * @param int[] $productsIds товары и предложения
     * @param int[] $catalogTypePrices тип цен
     */
    public function __construct(array $productsIds, array $catalogTypePrices)
    {
        $this->productsIds = $productsIds;
        $this->catalogTypePrices = $catalogTypePrices;
        parent::__construct($this->makeBasket());
    }

    protected function makeBasket(): BasketBase
    {
        $basket = Basket::create($this->getSiteId());
        $priceRows = $this->getProductPriceRows();
        foreach ($this->productsIds as $productId) {
            try {
                $priceRow = $priceRows[$productId];
                if (empty($priceRow)) continue;
                $item = $basket->createItem('catalog', $productId);
                $item->setFieldsNoDemand([
                    'PRODUCT_ID' => $productId,
                    'QUANTITY' => 1,
                    'PRICE' => $priceRow['PRICE'],
                    'BASE_PRICE' => $priceRow['PRICE'],
                    'CURRENCY' => $priceRow['CURRENCY'],
                    'PRODUCT_PRICE_ID' => $priceRow['ID'],
                    'LID' => $this->getSiteId(),
                    'CAN_BUY' => 'Y',
                    'DELAY' => 'N',
                    'PRICE_TYPE_ID' => $priceRow['CATALOG_GROUP_ID'],
                ]);

                $this->basketCodes[$productId] = $item->getBasketCode();
            } catch (\Exception $e) {
            }
        }

        return $basket;
    }

    protected function getProductPriceRows(): array
    {
        $rows = [];
        $dbPrice = \Bitrix\Catalog\PriceTable::getList([
            'select' => ['PRICE', 'CURRENCY', 'PRODUCT_ID', 'CATALOG_GROUP_ID'],
            'filter' => ['@PRODUCT_ID' => $this->productsIds, '@CATALOG_GROUP_ID' => $this->catalogTypePrices]
        ]);
        while ($priceRow = $dbPrice->fetch()) {
            if (Price::getBaseCurrency() != $priceRow['CURRENCY']) {
                $priceRow['PRICE'] = \CCurrencyRates::ConvertCurrency(
                    $priceRow['PRICE'],
                    $priceRow['CURRENCY'],
                    Price::getBaseCurrency()
                );
                $priceRow['CURRENCY'] = Price::getBaseCurrency();
            }
            $rows[(int)$priceRow['PRODUCT_ID']] = $priceRow;
        }
        return $rows;
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
