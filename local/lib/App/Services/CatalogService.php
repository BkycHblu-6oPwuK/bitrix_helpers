<?php

declare(strict_types=1);

namespace App\Services;

class CatalogService extends \Beeralex\Catalog\Service\CatalogService
{
    public function getProductsWithOffers(
        array $productIds,
        bool $isAvailable = true,
        bool $applyDiscounts = false,
        bool $withProperties = false,
        bool $withOfferTree = false,
    ): array {
        if (empty($productIds)) {
            return [];
        }

        $products = $this->productsRepository->getProducts($productIds, $isAvailable, ['*', 'PRICE', 'PRICE.CATALOG_GROUP', 'STORE_PRODUCT', 'CATALOG', 'BRAND_REF', 'MORE_PHOTO']);
        $offersByProduct = $this->offersRepository->getOffersByProductIds($productIds, $isAvailable);

        foreach ($products as &$product) {
            $offers = $offersByProduct[$product['ID']] ?? [];
            $productUrl = $product['DETAIL_PAGE_URL'] ?? '';

            $product['OFFERS'] = array_map(fn($offer) => $this->withOfferUrl($offer, $productUrl), $offers);
        }
        unset($product);

        if ($withProperties || $withOfferTree) {
            $this->propertyService->attachProperties($products);
        }

        if ($applyDiscounts) {
            $this->applyDiscounts($products);
        }

        foreach ($products as &$product) {
            if ($withOfferTree) {
                $product['OFFER_TREE'] = $this->propertyService->buildOfferTree($product['OFFERS']);
            }
            $this->setPreselectedOffer($product, null);
        }
        unset($product);

        return $products;
    }

    public function getProducts(
        array $productIds,
        bool $isAvailable = true,
        bool $applyDiscounts = false,
        bool $withProperties = false,
    ): array {
        if (empty($productIds)) {
            return [];
        }

        $products = $this->productsRepository->getProducts($productIds, $isAvailable, ['*', 'PRICE', 'PRICE.CATALOG_GROUP', 'STORE_PRODUCT', 'CATALOG', 'BRAND_REF', 'MORE_PHOTO']);

        if ($withProperties) {
            $this->propertyService->attachProperties($products);
        }

        if ($applyDiscounts) {
            $this->applyDiscounts($products);
        }

        return $products;
    }
}
