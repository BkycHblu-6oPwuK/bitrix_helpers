<?php

namespace Beeralex\Catalog\Service;

use Beeralex\Catalog\Contracts\OfferRepositoryContract;
use Beeralex\Catalog\Contracts\ProductRepositoryContract;
use Beeralex\Catalog\Discount\ProductsDiscount;
use Beeralex\Catalog\Repository\CatalogViewedProductRepository;
use Beeralex\Catalog\Repository\PriceTypeRepository;
use Beeralex\Core\Service\CatalogService as CoreCatalogService;
use Bitrix\Main\Loader;

class CatalogService extends CoreCatalogService
{
    public function __construct(
        protected readonly ProductRepositoryContract $productsRepository,
        protected readonly OfferRepositoryContract $offersRepository,
        protected readonly CatalogViewedProductRepository $viewedProductRepository,
        protected readonly PriceTypeRepository $priceTypeRepository,
    ) 
    {
        Loader::includeModule('sale');
        Loader::includeModule('catalog');
    }

    /**
     * Возвращает товары с их предложениями (и опционально скидками).
     */
    public function getProductsWithOffers(array $productIds, bool $isAvailable = true, bool $applyDiscounts = false): array
    {
        if (empty($productIds)) {
            return [];
        }

        $products = $this->productsRepository->getProducts($productIds, $isAvailable);
        $offersByProduct = $this->offersRepository->getOffersByProductIds($productIds, $isAvailable);
        foreach ($products as &$product) {
            $offers = $offersByProduct[$product['ID']] ?? [];
            if (empty($offers)) {
                continue;
            }

            $product['OFFERS'] = array_map(function ($offer) {
                return $offer;
            }, $offers);

            $product['PRESELECTED_OFFER'] = $offers[0];
        }

        if ($applyDiscounts) {
            $this->applyDiscounts($products);
        }

        return $products;
    }

    protected function updateProductPrices(array &$product, float $discountedPrice): void
    {
        $basePriceValue = 0.0;
        $basePriceId = $this->priceTypeRepository->getBasePriceId();
        $basePriceData = null;

        $prices = $product['PRICE'] ?? [];
        if (!is_array($prices)) {
            $prices = [];
        }

        if (isset($prices['PRICE']) || (isset($prices['ID']) && !isset($prices[0]))) {
            $prices = [$prices];
            $product['PRICE'] = $prices;
        }

        foreach ($prices as $price) {
            $priceValue = (float)($price['PRICE'] ?? 0.0);
            $groupId = (int)($price['CATALOG_GROUP_ID'] ?? 0);

            if ($groupId === $basePriceId) {
                $basePriceValue = $priceValue;
                $basePriceData = $price;
            }
        }

        if ($basePriceValue <= 0) {
            $basePriceValue = $discountedPrice;
        }

        if ($discountedPrice < $basePriceValue) {
            $templatePrice = $basePriceData ?? ($prices[0] ?? []);
            $newPrice = $templatePrice;
            $newPrice['ID'] = 0;
            $newPrice['CATALOG_GROUP_ID'] = 0;
            $newPrice['PRICE'] = $discountedPrice;
            $newPrice['PRICE_SCALE'] = $discountedPrice;
            $newPrice['TIMESTAMP_X'] = new \Bitrix\Main\Type\DateTime();
            
            if (isset($newPrice['CATALOG_GROUP'])) {
                $newPrice['CATALOG_GROUP']['ID'] = 0;
                $newPrice['CATALOG_GROUP']['NAME'] = 'Discount Price';
                $newPrice['CATALOG_GROUP']['BASE'] = 'N';
                $newPrice['CATALOG_GROUP']['XML_ID'] = 'discount_price';
            }
            
            $product['PRICE'][] = $newPrice;
        }
    }

    /**
     * Применяет скидки ко всем товарам и предложениям.
     */
    protected function applyDiscounts(array &$products): void
    {
        $ids = [];
        foreach ($products as $product) {
            $ids[] = $product['ID'];
            foreach ($product['OFFERS'] ?? [] as $offer) {
                $ids[] = $offer['ID'];
            }
        }

        if (empty($ids)) {
            return;
        }

        $discount = new ProductsDiscount($ids, [
            $this->priceTypeRepository->getBasePriceId(),
        ]);

        foreach ($products as &$product) {
            if ($price = $discount->getPriceByProductId($product['ID'])) {
                $this->updateProductPrices($product, $price);
            }
            $product['OFFERS'] ??= [];
            foreach ($product['OFFERS'] as &$offer) {
                if ($price = $discount->getPriceByProductId($offer['ID'])) {
                    $this->updateProductPrices($offer, $price);
                }
            }
        }
    }

    /**
     * Получает ID просмотренных пользователем товаров.
     */
    public function getViewedProductsIds(int $currentElementId): array
    {
        $skipUserInit = false;
        if (!\Bitrix\Catalog\Product\Basket::isNotCrawler()) {
            $skipUserInit = true;
        }

        $basketUserId = (int)\Bitrix\Sale\Fuser::getId($skipUserInit);
        if ($basketUserId <= 0) {
            return [];
        }

        return $this->viewedProductRepository->getViewedProductIds(
            $this->productsRepository->entityId,
            $basketUserId,
            $currentElementId
        );
    }
}
