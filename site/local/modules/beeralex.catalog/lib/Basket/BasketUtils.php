<?php

namespace Beeralex\Catalog\Basket;

use Beeralex\Catalog\Contracts\OfferRepositoryContract;
use Beeralex\Catalog\Contracts\ProductRepositoryContract;
use Bitrix\Sale\BasketBase;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\BasketPropertiesCollectionBase;
use Bitrix\Sale\PropertyValueCollectionBase;
use Beeralex\Catalog\Helper\PriceHelper;

class BasketUtils
{
    public function __construct(
       protected readonly ProductRepositoryContract $productsRepository,
       protected readonly OfferRepositoryContract $offersRepository,
       protected readonly BasketBase $basket
    ) {}

    public function getOffersIds()
    {
        $result = [];

        foreach ($this->basket->getBasketItems() as $basketItem) {

            $result[$basketItem->getProductId()] = $basketItem->getProductId();
        }
        return $result;
    }

    public function getItems()
    {
        // Получаем идентификаторы торговых предложений
        $offersIds = static::getOffersIds();

        // Получаем идентификаторы товаров и объединяем с offersIds - запишутся только те, что в корзине не офферы
        $productsIds = $this->offersRepository->getProductsIdsByOffersIds($offersIds) + $offersIds;

        // Получаем информацию о товарах
        $products = $this->productsRepository->getProducts(array_values($productsIds));

        // Получаем информацию о торговых предложениях
        $offers = $this->offersRepository->getOffersByIds($offersIds);

        // Формируем результат
        $basketItems = [];
        /** @var BasketItem $basketItem */
        foreach ($this->basket->getBasketItems() as $basketItem) {
            $offerId = $basketItem->getProductId();
            $productId = $productsIds[$offerId] ?? $offerId;
            $isOffer = $offerId !== $productId;

            if ($isOffer) {
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

            if ($isOffer) {
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

    public function getPropertyValues(BasketPropertiesCollectionBase $collection)
    {
        $values = [];
        /** @var PropertyValueCollectionBase $item */
        foreach ($collection as $item) {
            $values[$item->getField('CODE')] = $item->getField('VALUE');
        }
        return $values;
    }
}
