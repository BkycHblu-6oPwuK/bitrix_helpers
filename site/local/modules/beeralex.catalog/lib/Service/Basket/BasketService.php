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
                if (!$resultChangeQuantity->isSuccess()) {
                    $result->addErrors($resultChangeQuantity->getErrors());
                }
            }
        } else {
            $result = $this->addNewItem($offerId, $quantity);
        }

        if ($result->isSuccess()) {
            return $this->basket->save();
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
                    $result = $basketItem->setField('QUANTITY', $newQuantity);
                } else {
                    $result = $basketItem->delete();
                }
            }
        }

        if ($result->isSuccess()) {
            return $this->basket->save();
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
                if (!$deleteResult->isSuccess()) {
                    return $deleteResult;
                }
            }
            $saveResult = $this->basket->save();
            if (!$saveResult->isSuccess()) {
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
            if (!$deleteResult->isSuccess()) {
                return $deleteResult;
            }
        }
        return $this->basket->save();
    }

    public function getIds()
    {
        return array_values($this->basketUtils->getOffersIds());
    }

    public function getItems(): array
    {
        if ($this->basket->count() > 0) {
            $basketItems = $this->basketUtils->getItems($this->basket);
            foreach ($basketItems as &$basketItem) {
                $originalPrice = $basketItem['PRICE'];
                $finalPrice = 0;
                $discountPrice = $this->discountService->getPrice($basketItem['CODE']);
                
                if ($discountPrice && $discountPrice < $originalPrice) {
                    $finalPrice = $discountPrice;
                    $basketItem['OLD_PRICE'] = $originalPrice;
                    $basketItem['OLD_PRICE_FORMATTED'] = $this->priceService->format($originalPrice);
                    $basketItem['FULL_OLD_PRICE'] = $originalPrice * $basketItem['QUANTITY'];
                    $basketItem['FULL_OLD_PRICE_FORMATTED'] = $this->priceService->format($basketItem['FULL_OLD_PRICE']);
                    $basketItem['DISCOUNT_PERCENT'] = $this->priceService->getSalePercent($originalPrice, $finalPrice);
                } else {
                    $finalPrice = $originalPrice;
                    $basketItem['OLD_PRICE'] = null;
                    $basketItem['OLD_PRICE_FORMATTED'] = null;
                    $basketItem['FULL_OLD_PRICE'] = null;
                    $basketItem['FULL_OLD_PRICE_FORMATTED'] = null;
                    $basketItem['DISCOUNT_PERCENT'] = null;
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
            if ($item['FULL_OLD_PRICE'] !== null) {
                $totalDiscount += $item['FULL_OLD_PRICE'] - $item['FULL_PRICE'];
            }
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

    public function changeProductQuantityInBasket(int $offerId, int $quantity): Result
    {
        $checkQuantityResult = $this->checkQuantity($offerId, $quantity);
        if (!$checkQuantityResult->isSuccess()) {
            return $checkQuantityResult;
        }
        
        $result = new Result();
        $basketItem = $this->getExistBasketItems($offerId)[0] ?? null;
        
        if ($basketItem) {
            $setFieldResult = $basketItem->setField('QUANTITY', $quantity);
            if (!$setFieldResult->isSuccess()) {
                return $setFieldResult;
            }
            return $this->basket->save();
        } else {
            $result->addError(new \Bitrix\Main\Error("Basket item with offerId {$offerId} not found", 'basket'));
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

    public function getOffersQuantity(): int
    {
        return count($this->basket->getBasketItems());
    }

    public function getBasket(): BasketBase
    {
        return $this->basket;
    }

    protected function changeExistedItemQuantity(BasketItem $basketItem, int $quantity): Result
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

    protected function addNewItem(int $offerId, int $quantity): Result
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
        return $this->basket->save();
    }

    protected function getFields(int $quantity): array
    {
        return [
            'QUANTITY' => $quantity,
            'CURRENCY' => CurrencyManager::getBaseCurrency(),
            'LID' => $this->basket->getSiteId(),
            'PRODUCT_PROVIDER_CLASS' => CatalogProvider::class,
        ];
    }

    protected function checkQuantity(int $offerId, int $quantity): Result
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
