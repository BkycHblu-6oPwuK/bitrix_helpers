<?php

namespace Beeralex\Catalog\Discount;

use Beeralex\Catalog\Helper\PriceHelper;
use Bitrix\Catalog\Discount\DiscountManager;
use Bitrix\Main\Loader;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketBase;

Loader::includeModule('catalog');

class ProductDiscount extends Discount
{
    protected int $productId;
    protected int $priceTypeId;
    protected int|string $basketCode;

    /**
     * @param int $productId товар или предложение
     * @param int $priceTypeId тип цены
     */
    public function __construct(int $productId, int $priceTypeId)
    {
        $this->productId = $productId;
        $this->priceTypeId = $priceTypeId;
        parent::__construct($this->makeBasket());
    }

    protected function makeBasket(): BasketBase
    {
        $basket = Basket::create($this->getSiteId());
        try {
            $priceRow = $this->getProductPriceRow();
            $item = $basket->createItem('catalog', $this->productId);
            $item->setFieldsNoDemand([
                'PRODUCT_ID' => $this->productId,
                'QUANTITY' => 1,
                'PRICE' => $priceRow['PRICE'],
                'BASE_PRICE' => $priceRow['PRICE'],
                'CURRENCY' => $priceRow['CURRENCY'],
                'PRODUCT_PRICE_ID' => $priceRow['ID'],
                'LID' => $this->getSiteId(),
                'CAN_BUY' => 'Y',
                'DELAY' => 'N',
                'PRICE_TYPE_ID' => $this->priceTypeId
            ]);
            $this->basketCode = $item->getBasketCode();
        } catch (\Exception $e) {
        }

        return $basket;
    }

    protected function getProductPriceRow(): array
    {
        $priceRow = DiscountManager::getPriceDataByProductId($this->productId, $this->priceTypeId);
        if (empty($priceRow)) {
            throw new \Exception();
        }
        if (PriceHelper::getBaseCurrency() != $priceRow['CURRENCY']) {
            $priceRow['PRICE'] = \CCurrencyRates::ConvertCurrency(
                $priceRow['PRICE'],
                $priceRow['CURRENCY'],
                PriceHelper::getBaseCurrency()
            );
            $priceRow['CURRENCY'] = PriceHelper::getBaseCurrency();
        }
        return $priceRow;
    }

    public function getPrice(null|int|string $basketCode = null): ?float
    {
        return parent::getPrice($basketCode ?? $this->basketCode);
    }
}
