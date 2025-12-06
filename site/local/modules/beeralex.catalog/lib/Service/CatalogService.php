<?php

namespace Beeralex\Catalog\Service;

use Beeralex\Catalog\Contracts\OfferRepositoryContract;
use Beeralex\Catalog\Contracts\ProductRepositoryContract;
use Beeralex\Catalog\Repository\CatalogViewedProductRepository;
use Beeralex\Catalog\Repository\PriceTypeRepository;
use Beeralex\Catalog\Service\Discount\DiscountFactory;
use Beeralex\Core\Service\CatalogService as CoreCatalogService;
use Beeralex\Core\Service\SortingService;
use Bitrix\Main\Context;
use Bitrix\Main\Web\Uri;

class CatalogService extends CoreCatalogService
{
    public function __construct(
        protected readonly ProductRepositoryContract $productsRepository,
        protected readonly OfferRepositoryContract $offersRepository,
        protected readonly CatalogViewedProductRepository $viewedProductRepository,
        protected readonly PriceTypeRepository $priceTypeRepository,
        protected readonly SortingService $sortingService,
        protected readonly DiscountFactory $discountFactory,
        protected readonly CatalogSectionsService $catalogSectionsService,
        protected readonly SearchService $searchService
    ) {}

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
            $productUrl = $product['DETAIL_PAGE_URL'] ?? '';
            if (empty($offers)) {
                continue;
            }

            $product['OFFERS'] = array_map(function ($offer) use ($productUrl) {
                $offerUrlTemplate = $offer['IBLOCK']['DETAIL_PAGE_URL'] ?? '';
                $offerUrl = null;
                if($offerUrlTemplate) {
                    $offerUrl = str_replace(
                        ['#PRODUCT_URL#', '#CODE#', '#ID#'],
                        [$productUrl, $offer['CODE'], $offer['ID']],
                        $offerUrlTemplate
                    );
                }
                $offer['DETAIL_PAGE_URL'] = $offerUrl;
                return $offer;
            }, $offers);

            $product['PRESELECTED_OFFER'] = $product['OFFERS'][0];
        }

        if ($applyDiscounts) {
            $this->applyDiscounts($products);
        }

        return $products;
    }

    public function makeUrl(string $url): string
    {
        $requestedSortId = $this->sortingService->getRequestedSortIdOrDefault();
        $query = Context::getCurrent()->getRequest()->get(SearchService::REQUEST_PARAM);

        $uri = new Uri($url);

        if ($requestedSortId != $this->sortingService->getDefaultSortId()) {
            $uri->addParams([SortingService::REQUEST_PARAM => $requestedSortId]);
        }
        if ($query) {
            $uri->addParams([SearchService::REQUEST_PARAM => $query]);
        }

        return $uri->getUri();
    }

    public function search(string $query, int $searchLimit = 50, int $realLimit = 7): array
    {
        $result = [];
        $productsIds = $this->searchService->getProductsIds($query, $searchLimit);
        $sections = $this->catalogSectionsService->getSections($productsIds);
        $productsIds = array_splice($productsIds, 0, $realLimit);
        $result['PRODUCTS'] = $this->getProductsWithOffers($productsIds);
        $result['SECTIONS'] = $sections;
        return $result;
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

        $discount = $this->discountFactory->createProductsDiscount($ids, [
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
}
