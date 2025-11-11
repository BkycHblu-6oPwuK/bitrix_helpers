<?php
namespace Beeralex\Catalog\Basket;

use Bitrix\Currency\CurrencyManager;
use Bitrix\Sale\BasketBase;
use Bitrix\Sale\BasketItem;
use Beeralex\Catalog\Discount\Coupons;
use Beeralex\Catalog\Discount\Discount;
use Beeralex\Catalog\Helper\PriceHelper;

/**
 * Обертка над корзиной
 */
class BasketFacade
{
    public function __construct(
        protected readonly BasketBase $basket,
        protected readonly BasketUtils $basketUtils,
        protected readonly Coupons $coupons,
        protected readonly Discount $discount,
    )
    {}

    public function add(int $offerId, int $quantity = 1)
    {
        $basketItems = $this->getExitstBasketItems($offerId);
        if (!empty($basketItems)) {
            foreach ($basketItems as $basketItem) {
                $this->changeExistedItemQuantity($basketItem, $quantity);
            }
        } else {
            $this->addNewItem($offerId, $quantity);
        }
        return $this;
    }

    public function removeOne(int $offerId)
    {
        $basketItems = $this->getExitstBasketItems($offerId);
        if (!empty($basketItems)) {
            foreach ($basketItems as $basketItem) {
                $newQuantity = $basketItem->getQuantity() - 1;
                if ($newQuantity > 0) {
                    $basketItem->setField('QUANTITY', $newQuantity);
                } else {
                    $basketItem->delete();
                }
            }
        }
        return $this;
    }

    private function changeExistedItemQuantity(BasketItem $basketItem, int $quantity)
    {
        $this->checkQuantity($basketItem->getProductId(), $basketItem->getQuantity() + $quantity);
        $basketItem->setField('QUANTITY', $basketItem->getQuantity() + $quantity);
        $basketItem->save();
    }

    private function addNewItem(int $offerId, int $quantity)
    {
        $this->checkQuantity($offerId, $quantity);
        $basketItem = $this->basket->createItem('catalog', $offerId);
        $basketItem->setFields($this->getFields($quantity));
        $basketItem->save();
    }

    private function getFields(int $quantity): array
    {
        return [
            'QUANTITY' => $quantity,
            'CURRENCY' => CurrencyManager::getBaseCurrency(),
            'LID' => $this->basket->getSiteId(),
            'PRODUCT_PROVIDER_CLASS' => '\Bitrix\Catalog\Product\CatalogProvider',
        ];
    }

    private function checkQuantity(int $offerId, int $quantity)
    {
        if ($quantity <= 0) {
            throw new \InvalidArgumentException('Количество должно быть больше 0');
        }

        $availableQuantity = \Bitrix\Catalog\ProductTable::query()->setSelect(['QUANTITY'])->where('ID', $offerId)->fetch()['QUANTITY'] ?? 0;
        if ($quantity > $availableQuantity) {
            throw new \RuntimeException('Товара нет в наличии');
        }
    }

    public function remove(int $offerId)
    {
        $basketItems = $this->getExitstBasketItems($offerId);
        if (!empty($basketItems)) {
            foreach ($basketItems as $basketItem) {
                $basketItem->delete();
            }
            $this->basket->save();
        } else {
            throw new \InvalidArgumentException("Не найден товар ({$offerId}) в корзине");
        }
        return $this;
    }

    public function removeAll()
    {
        foreach ($this->basket->getBasketItems() as $item) {
            $item->delete();
        }
        return $this;
    }

    public function getItems(): array
    {
        if ($this->basket->count() > 0) {
            $basketItems = $this->basketUtils->getItems($this->basket);

            foreach ($basketItems as &$basketItem) {
                $finalPrice = 0;
                $discountPrice = $this->discount->getPrice($basketItem['code']);
                if ($discountPrice) {
                    $finalPrice = $discountPrice;
                    $basketItem['discountPercent'] = PriceHelper::getSalePercent($basketItem['oldPrice'], $finalPrice);
                } else {
                    $finalPrice = $basketItem['price'];
                }

                $basketItem['price'] = $finalPrice;
                $basketItem['priceFormatted'] = PriceHelper::format($finalPrice);
                $basketItem['fullPrice'] = $finalPrice * $basketItem['quantity'];
                $basketItem['fullPriceFormatted'] = PriceHelper::format($basketItem['fullPrice']);
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
        $coupon = $this->coupons->getApplyedCoupon();
        foreach ($items as $item) {
            $totalPrice += $item['fullPrice'];
            $totalDiscount += $item['fullOldPrice'] - $item['fullPrice'];
        }

        return [
            'items' => $items,
            'coupon' => $coupon,
            'summary' => [
                'totalQuantity' => $totalQuantity,
                'totalPrice' => $totalPrice,
                'totalPriceFormatted' => PriceHelper::format($totalPrice),
                'totalDiscount' => $totalDiscount,
                'totalDiscountFormatted' => PriceHelper::format($totalDiscount)
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

    public function save()
    {
        return $this->basket->save();
    }

    public function changeProductQuantityInBasket(int $productId, int $qty): void
    {
        $this->checkQuantity($productId, $qty);
        if ($basketItem = $this->basket->getExistsItem('catalog', $productId)) {
            $basketItem->setField('QUANTITY', $qty);
            $this->basket->save();
        } else {
            throw new \Exception("Basket item with productId {$productId} not found");
        }
    }

    /**
     * @throws \Exception
     */
    public function applyCoupon(string $couponCode)
    {
        return $this->coupons->applyCoupon($couponCode);
    }

    public function getExitstBasketItems(int $offerId): array
    {
        return $this->basket->getExistsItems('catalog', $offerId, null);
    }
}
