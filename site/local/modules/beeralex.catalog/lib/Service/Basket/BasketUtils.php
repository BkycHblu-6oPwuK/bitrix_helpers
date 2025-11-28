<?php
namespace Beeralex\Catalog\Service\Basket;

use Beeralex\Catalog\Contracts\OfferRepositoryContract;
use Beeralex\Catalog\Contracts\ProductRepositoryContract;
use Bitrix\Sale\BasketBase;
use Bitrix\Sale\BasketItem;
use Bitrix\Sale\BasketPropertiesCollectionBase;
use Bitrix\Sale\PropertyValueCollectionBase;
use Beeralex\Catalog\Service\PriceService;

class BasketUtils
{
    public function __construct(
       protected readonly ProductRepositoryContract $productsRepository,
       protected readonly OfferRepositoryContract $offersRepository,
       protected readonly BasketBase $basket,
       protected readonly PriceService $priceService
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
                'ID' => $basketItem->getId(),
                'CODE' => $basketItem->getBasketCode(),
                'OFFER_ID' => $offerId,
                'PRODUCT_ID' => $productId,
                'IS_OFFER' => $isOffer,
                'QUANTITY' => $basketItem->getQuantity(),
                'PRICE' => $basketItem->getPrice(),
                'PRICE_FORMATTED' => $this->priceService->format($basketItem->getPrice()),
                'FULL_PRICE' => $basketItem->getPrice() * $basketItem->getQuantity(),
                'FULL_PRICE_FORMATTED' => $this->priceService->format($basketItem->getPrice() * $basketItem->getQuantity()),
            ];

            $basketInfo['URL'] = $productInfo['URL'] ?? $offerInfo['URL'];
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
