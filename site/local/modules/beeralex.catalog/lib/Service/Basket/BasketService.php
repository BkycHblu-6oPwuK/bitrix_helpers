<?php

namespace Beeralex\Catalog\Service\Basket;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Sale\BasketBase;
use Bitrix\Sale\BasketItem;
use Beeralex\Catalog\Service\Discount\CouponsService;
use Beeralex\Catalog\Service\Discount\DiscountService;
use Beeralex\Catalog\Service\PriceService;
use Bitrix\Catalog\Product\CatalogProvider;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

/**
 * Обертка над корзиной
 */
class BasketService
{
    protected string $basketModuleId = 'catalog';

    public function __construct(
        protected readonly BasketBase $basket,
        protected readonly BasketUtils $basketUtils,
        protected readonly CouponsService $couponsService,
        protected readonly DiscountService $discountService,
        protected readonly PriceService $priceService
    ) {}

    public function setBasketModuleId(string $moduleId): static
    {
        $this->basketModuleId = $moduleId;
        return $this;
    }

    public function increment(int $offerId, int $quantity = 1): Result
    {
        $result = new Result();
        $basketItems = $this->getExistBasketItems($offerId);
        if (!empty($basketItems)) {
            foreach ($basketItems as $basketItem) {
                $resultChangeQuantity = $this->changeExistedItemQuantity($basketItem, $quantity);
                $result->addErrors($resultChangeQuantity->getErrors());
            }
        } else {
            return $this->addNewItem($offerId, $quantity);
        }
        return $result;
    }

    public function decrement(int $offerId, int $quantity = 1): Result
    {
        $result = new Result();
        $basketItems = $this->getExistBasketItems($offerId);
        if (!empty($basketItems)) {
            foreach ($basketItems as $basketItem) {
                $newQuantity = $basketItem->getQuantity() - $quantity;
                if ($newQuantity > 0) {
                    return $basketItem->setField('QUANTITY', $newQuantity);
                } else {
                    return $basketItem->delete();
                }
            }
        }
        return $result;
    }

    public function remove(int $offerId): Result
    {
        $basketItems = $this->getExistBasketItems($offerId);
        $result = new Result();
        if (!empty($basketItems)) {
            foreach ($basketItems as $basketItem) {
                $deleteResult = $basketItem->delete();
                if(!$deleteResult->isSuccess()) {
                    return $deleteResult;
                }
            }
            $saveResult = $this->basket->save();
            if(!$saveResult->isSuccess()) {
                return $saveResult;
            }
        } else {
            $result->addError(new \Bitrix\Main\Error("Товар ({$offerId}) не найден в корзине", 'basket'));
        }
        return $result;
    }

    public function removeAll(): Result
    {
        /** @var BasketItem */
        foreach ($this->basket->getBasketItems() as $item) {
            $deleteResult = $item->delete();
            if(!$deleteResult->isSuccess()) {
                return $deleteResult;
            }
        }
        return new Result();
    }

    public function getItems(): array
    {
        if ($this->basket->count() > 0) {
            $basketItems = $this->basketUtils->getItems($this->basket);

            foreach ($basketItems as &$basketItem) {
                $finalPrice = 0;
                $discountPrice = $this->discountService->getPrice($basketItem['CODE']);
                if ($discountPrice) {
                    $finalPrice = $discountPrice;
                    $basketItem['DISCOUNT_PERCENT'] = $this->priceService->getSalePercent($basketItem['PRICE'], $finalPrice);
                } else {
                    $finalPrice = $basketItem['PRICE'];
                }

                $basketItem['PRICE'] = $finalPrice;
                $basketItem['PRICE_FORMATTED'] = $this->priceService->format($finalPrice);
                $basketItem['FULL_PRICE'] = $finalPrice * $basketItem['QUANTITY'];
                $basketItem['FULL_PRICE_FORMATTED'] = $this->priceService->format($basketItem['FULL_PRICE']);
            }

            return $basketItems;
        } else {
            return [];
        }
    }

    public function getBasketData(): array
    {
        $items = $this->getItems();

        $totalQuantity = $this->getOffersQuantity();
        $totalPrice = 0;
        $totalDiscount = 0;
        $coupon = $this->couponsService->getApplyedCoupon();
        foreach ($items as $item) {
            $totalPrice += $item['FULL_PRICE'];
            $totalDiscount += $item['FULL_OLD_PRICE'] - $item['FULL_PRICE'];
        }

        return [
            'ITEMS' => $items,
            'COUPON' => $coupon,
            'SUMMARY' => [
                'TOTAL_QUANTITY' => $totalQuantity,
                'TOTAL_PRICE' => $totalPrice,
                'TOTAL_PRICE_FORMATTED' => $this->priceService->format($totalPrice),
                'TOTAL_DISCOUNT' => $totalDiscount,
                'TOTAL_DISCOUNT_FORMATTED' => $this->priceService->format($totalDiscount)
            ]
        ];
    }

    public function getOffersQuantity()
    {
        return count($this->basket->getBasketItems());
    }

    public function getBasket(): BasketBase
    {
        return $this->basket;
    }

    public function save(): Result
    {
        return $this->basket->save();
    }

    public function changeProductQuantityInBasket(int $productId, int $quantity): Result
    {
        $checkQuantityResult = $this->checkQuantity($productId, $quantity);
        if (!$checkQuantityResult->isSuccess()) {
            return $checkQuantityResult;
        }
        $result = new Result();
        if ($basketItem = $this->getExistBasketItems($this->basketModuleId, $productId)[0] ?? null) {
            $basketItem->setField('QUANTITY', $quantity);
            return $this->basket->save();
        } else {
            $result->addError(new \Bitrix\Main\Error("Basket item with productId {$productId} not found", 'basket'));
        }
        return $result;
    }

    /**
     * @throws \Exception
     */
    public function applyCoupon(string $couponCode): Result
    {
        return $this->couponsService->applyCoupon($couponCode);
    }

    /**
     * @return BasketItem[]
     */
    public function getExistBasketItems(int $offerId): array
    {
        return $this->basket->getExistsItems($this->basketModuleId, $offerId, null);
    }

    private function changeExistedItemQuantity(BasketItem $basketItem, int $quantity): Result
    {
        $checkQuantityResult = $this->checkQuantity($basketItem->getProductId(), $basketItem->getQuantity() + $quantity);
        if (!$checkQuantityResult->isSuccess()) {
            return $checkQuantityResult;
        }
        $result = new Result();
        $setFieldResult = $basketItem->setField('QUANTITY', $basketItem->getQuantity() + $quantity);
        if (!$setFieldResult->isSuccess()) {
            foreach ($setFieldResult->getErrors() as $error) {
                $result->addError(new Error($error->getMessage(), 'basket'));
            }
        }
        return $result;
    }

    private function addNewItem(int $offerId, int $quantity): Result
    {
        $checkQuantityResult = $this->checkQuantity($offerId, $quantity);
        if (!$checkQuantityResult->isSuccess()) {
            return $checkQuantityResult;
        }
        $basketItem = $this->basket->createItem($this->basketModuleId, $offerId);
        $setFieldsResult = $basketItem->setFields($this->getFields($quantity));
        if (!$setFieldsResult->isSuccess()) {
            $result = new Result();
            foreach ($setFieldsResult->getErrors() as $error) {
                $result->addError(new Error($error->getMessage(), 'basket'));
            }
            return $result;
        }
        $saveResult = $basketItem->save();
        if (!$saveResult->isSuccess()) {
            $result = new Result();
            foreach ($saveResult->getErrors() as $error) {
                $result->addError(new Error($error->getMessage(), 'basket'));
            }
        }
        return new Result();
    }

    private function getFields(int $quantity): array
    {
        return [
            'QUANTITY' => $quantity,
            'CURRENCY' => CurrencyManager::getBaseCurrency(),
            'LID' => $this->basket->getSiteId(),
            'PRODUCT_PROVIDER_CLASS' => CatalogProvider::class,
        ];
    }

    private function checkQuantity(int $offerId, int $quantity): Result
    {
        $result = new Result();
        if ($quantity <= 0) {
            $result->addError(new \Bitrix\Main\Error('Количество должно быть больше 0', 'basket'));
            return $result;
        }

        $availableQuantity = \Bitrix\Catalog\ProductTable::query()->setSelect(['QUANTITY'])->where('ID', $offerId)->fetch()['QUANTITY'] ?? 0;
        if ($quantity > $availableQuantity) {
            $result->addError(new \Bitrix\Main\Error('Товара нет в наличии', 'basket'));
            return $result;
        }
        return $result;
    }
}
