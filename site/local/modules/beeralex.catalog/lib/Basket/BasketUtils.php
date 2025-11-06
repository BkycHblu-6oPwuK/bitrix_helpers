<?php

namespace Beeralex\Catalog\Basket;

use Bitrix\Main\Loader;
use Bitrix\Sale\BasketBase;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\BasketPropertiesCollectionBase;
use Bitrix\Sale\PropertyValueCollectionBase;
use Beeralex\Catalog\Helper\PriceHelper;
use Beeralex\Catalog\Helper\ProductsHelper;

/**
 * Методы для работы с корзиной
 */
class BasketUtils
{

    /**
     * Получает идентификаторы всех торговых предложений в корзине
     *
     * @param BasketBase $basket
     * @return array
     */
    public static function getOffersIds(BasketBase $basket)
    {
        $result = [];

        foreach ($basket->getBasketItems() as $basketItem) {

            $result[$basketItem->getProductId()] = $basketItem->getProductId();

        }
        return $result;

    }

    public static function getItems(BasketBase $basket)
    {
        Loader::includeModule('catalog');

        // Получаем идентификаторы торговых предложений
        $offersIds = self::getOffersIds($basket);
        
        // Получаем идентификаторы товаров и объединяем с offersIds - запишутся только те, что в корзине не офферы
        $productsIds = ProductsHelper::getProductsIdsByOffersIds($offersIds) + $offersIds;
        
        // Получаем информацию о товарах
        $products = ProductsHelper::getProducts(array_values($productsIds));
        
        // Получаем информацию о торговых предложениях
        $offers = ProductsHelper::getOffersByIds($offersIds);

        // Формируем результат
        $basketItems = [];
        /** @var BasketItem $basketItem */
        foreach ($basket->getBasketItems() as $basketItem) {
            $offerId = $basketItem->getProductId();
            $productId = $productsIds[$offerId] ?? $offerId;
            $isOffer = $offerId !== $productId;
            
            if($isOffer) {
                $offerInfo = $offers[$offerId];
                $productInfo = $products[$productId];
            } else {
                $offerInfo = $products[$offerId];
                $productInfo = [];
            }

            if (empty($offerInfo) || ($isOffer && empty($productInfo))) {
                continue;
            }

            $basketInfo = [
                'id' => $basketItem->getId(),
                'code' => $basketItem->getBasketCode(),
                'offerId' => $offerId,
                'productId' => $productId,
                'isOffer' => $isOffer,
                'quantity' => $basketItem->getQuantity(),
                'price' => $basketItem->getPrice(),
                'priceFormatted' => PriceHelper::format($basketItem->getPrice()),
                'fullPrice' => $basketItem->getPrice() * $basketItem->getQuantity(),
                'fullPriceFormatted' => PriceHelper::format($basketItem->getPrice() * $basketItem->getQuantity()),
            ];

            $basketInfo['url'] = $productInfo['url'] ?? $offerInfo['url'];

            if($isOffer){
                $basketInfo['url'] .=  '?offerId=' . $offerId;
            }

            $basketInfo['oldPrice'] = $offerInfo['price']['oldPriceValue'];
            $basketInfo['oldPriceFormatted'] = $offerInfo['price']['oldPriceFormatted'];
            $basketInfo['fullOldPrice'] = $basketInfo['oldPrice'] * $basketItem->getQuantity();
            $basketInfo['fullOldPriceFormatted'] = PriceHelper::format($basketInfo['oldPrice'] * $basketItem->getQuantity());
            $basketInfo['discountPercent'] = PriceHelper::getSalePercent($basketInfo['oldPrice'], $basketItem->getPrice());

            $basketItems[] = array_merge(
                $productInfo ?? [],
                $offerInfo,
                $basketInfo
            );
        }
        return $basketItems;
    }

    public static function getPropertyValues(BasketPropertiesCollectionBase $collection)
    {
        $values = [];
        /** @var PropertyValueCollectionBase $item */
        foreach ($collection as $item) {
            $values[$item->getField('CODE')] = $item->getField('VALUE');
        }
        return $values;
    }
}
