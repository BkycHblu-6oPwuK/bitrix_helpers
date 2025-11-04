<?php

namespace App\Catalog\Service;

use App\Catalog\Contracts\OfferRepositoryContract;
use App\Catalog\Contracts\ProductRepositoryContract;
use App\Catalog\Discount\ProductsDiscount;
use App\Catalog\Helper\PriceHelper;
use App\Catalog\Helper\SizeComparatorHelper;
use Beeralex\Core\Helpers\CatalogHelper;
use Bitrix\Catalog\CatalogViewedProductTable;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\Type\DateTime;

class CatalogService
{
    public function __construct(
        private ProductRepositoryContract $productsRepository,
        private OfferRepositoryContract $offersRepository,
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
            $offers = $offersByProduct[$product['id']] ?? [];
            if (empty($offers)) {
                continue;
            }

            usort($offers, fn($a, $b) => SizeComparatorHelper::compare($a['razmer'] ?? '', $b['razmer'] ?? ''));

            $product['offers'] = array_map(function ($offer) use ($product) {
                $offer['url'] = $product['url'];
                return $offer;
            }, $offers);

            $product['preselectedOffer'] = $offers[0];
            $product['selectedOfferId'] = $offers[0]['id'];
            $product['promoProductID'] = $offers[0]['id'] ?? $product['id'];
        }

        if ($applyDiscounts) {
            $this->applyDiscounts($products);
        }

        return $products;
    }

    /**
     * Применяет скидки ко всем товарам и предложениям.
     */
    private function applyDiscounts(array &$products): void
    {
        $ids = [];
        foreach ($products as $product) {
            $ids[] = $product['id'];
            foreach ($product['offers'] ?? [] as $offer) {
                $ids[] = $offer['id'];
            }
        }

        if (empty($ids)) {
            return;
        }

        $discount = new ProductsDiscount($ids, [
            PriceHelper::getBasePriceId(),
            PriceHelper::getDiscountPriceId(),
        ]);

        foreach ($products as &$product) {
            if ($price = $discount->getPriceByProductId($product['id'])) {
                PriceHelper::modifyPrice($product['price'], $price);
            }

            foreach ($product['offers'] ?? [] as &$offer) {
                if ($price = $discount->getPriceByProductId($offer['id'])) {
                    PriceHelper::modifyPrice($offer['price'], $price);
                }
            }
        }
    }

    public function getViewedProductsIds(int $currentElementId): array
    {
        if (!Loader::includeModule('sale')) {
            return [];
        }

        $skipUserInit = false;
        if (!\Bitrix\Catalog\Product\Basket::isNotCrawler()) {
            $skipUserInit = true;
        }

        $basketUserId = (int)\Bitrix\Sale\Fuser::getId($skipUserInit);
        if ($basketUserId <= 0) {
            return [];
        }

        return array_values(CatalogViewedProductTable::getProductSkuMap(
            $this->productsRepository->entityId,
            0,
            $basketUserId,
            $currentElementId,
            15
        ));
    }

    public function getSameProductsIds(int $elementId, int $sectionId, int $cacheTtl = 0): array
    {
        if (!$elementId || !$sectionId) {
            return [];
        }

        $dbResult = CatalogHelper::addCatalogToQuery($this->productsRepository->query())
            ->setSelect(['ID'])
            ->where('ACTIVE', 'Y')
            ->where('CATALOG.AVAILABLE', 'Y')
            ->where('IBLOCK_SECTION_ID', $sectionId)
            ->whereNot('ID', $elementId)
            ->setLimit(15);
        if ($cacheTtl) {
            $dbResult = $dbResult->setCacheTtl($cacheTtl)->cacheJoins(true);
        }
        $dbResult = $dbResult->exec();

        $productsIds = [];
        while ($item = $dbResult->fetch()) {
            $productsIds[] = (int)$item['ID'];
        }

        return $productsIds;
    }

    public function getNewProductsIds(int $limit = 15, int $cacheTtl = 0): array
    {
        $date = (new DateTime())->add("-1 months");

        $dbResult = CatalogHelper::addCatalogToQuery($this->productsRepository->query())
            ->setSelect(['ID'])
            ->where('ACTIVE', 'Y')
            ->where('CATALOG.AVAILABLE', 'Y')
            ->where('DATE_CREATE', '>=', $date)
            ->setLimit($limit);

        if ($limit) {
            $dbResult = $dbResult->setLimit($limit);
        }
        if ($cacheTtl) {
            $dbResult = $dbResult->setCacheTtl($cacheTtl)->cacheJoins(true);
        }
        $dbResult = $dbResult->exec();

        $productsIds = [];
        while ($item = $dbResult->fetch()) {
            $productsIds[] = (int)$item['ID'];
        }
        return $productsIds;
    }

    public function getPopularProductsIds(int $limit = 15, int $cacheTtl = 0): array
    {
        $dbResult = CatalogHelper::addCatalogToQuery(CatalogViewedProductTable::query(), 'PRODUCT_ID')
            ->registerRuntimeField('PRODUCT', [
                'data_type' => $this->productsRepository->entityClass,
                'reference' => ['=this.PRODUCT_ID' => 'ref.ID'],
                'join_type' => 'INNER',
            ])
            ->setSelect([
                'PRODUCT_ID',
                'VIEWS' => new ExpressionField('VIEWS', 'COUNT(*)'),
            ])
            ->where('PRODUCT.ACTIVE', 'Y')
            ->where('CATALOG.AVAILABLE', 'Y')
            ->setGroup('PRODUCT_ID')
            ->setOrder(['VIEWS' => 'DESC']);

        if ($limit) {
            $dbResult = $dbResult->setLimit($limit);
        }
        if ($cacheTtl) {
            $dbResult = $dbResult->setCacheTtl($cacheTtl)->cacheJoins(true);
        }
        $dbResult = $dbResult->exec();

        $productsIds = [];
        while ($item = $dbResult->fetch()) {
            $productsIds[] = (int)$item['PRODUCT_ID'];
        }

        return $productsIds;
    }
}
