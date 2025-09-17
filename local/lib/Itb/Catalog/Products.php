<?php

namespace Itb\Catalog;

use Bitrix\Catalog\CatalogViewedProductTable;
use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Bitrix\Main\Type\DateTime;
use Itb\Catalog\Discount\ProductsDiscount;
use Itb\Catalog\CatalogHelper;
use Itb\Catalog\Repository\StoreRepository;

class Products
{
    const SLIDER_COUNT = 15;

    public static function getProductsAndOffers(array $productsIds, bool $isAvailable = true, bool $applyDiscounts = false): array
    {
        if (empty($productsIds)) {
            return [];
        }

        // Получаем информацию о товарах
        $products = self::getProducts($productsIds, $isAvailable);
        // Получаем информацию о торговых предложениях
        $offers = self::getOffersByProductsIds($productsIds, $isAvailable);
        // Объединяем полученную информацию
        foreach ($products as &$product) {
            // Формируем ссылку на торговое предложение
            if (empty($offers[$product['id']])) continue;

            $product['offers'] = array_map(function ($offer) use ($product) {
                //$offer['url'] = $product['url'] . '?offerId=' . $offer['id'];
                $offer['url'] = $product['url'];
                return $offer;
            }, $offers[$product['id']]);

            // Сортируем торговые предложения по размеру
            $comparator = new SizeComparator();
            usort($product['offers'], function ($a, $b) use ($comparator) {

                if (empty($a['razmer'])) {
                    return -1;
                }
                if (empty($b['razmer'])) {
                    return 1;
                }

                $result = $comparator->compare($a['razmer'], $b['razmer']);

                return $result;
            });

            $product['preselectedOffer'] = $product['offers'][0];
            // Выбираем торговое предложение с минимальной ценой
            // $minPrice = $product['offers'][0]['price']['priceValue'];
            // foreach ($product['offers'] as $offer) {
            //     if ($offer['price']['priceValue'] < $minPrice) {
            //         $product['preselectedOffer'] = $offer;
            //         $minPrice = $offer['price']['priceValue'];
            //     }
            // }
            $product['selectedOfferId'] = $product['preselectedOffer']['id'];

            $promoProductID = $product['id'];
            if (!empty($product['offers'][0]['id'])) {
                $promoProductID = $product['offers'][0]['id'];
            }

            $product['promoProductID'] = $promoProductID;
        }
        if ($applyDiscounts) {
            self::applyDiscounts($products);
        }
        return $products;
    }

    public static function getProducts(array $productIds, bool $isAvailable = true): array
    {
        if (empty($productIds)) {
            return [];
        }
        $basePriceId = Price::getBasePriceId();
        $discountPriceId = Price::getDiscountPriceId();

        $productsInfo = [];
        $basePrices = [];
        $discountPrices = [];

        $query = self::buildProductQuery($productIds, $isAvailable);
        $result = $query->exec();

        while ($product = $result->fetch()) {
            $productId = (int)$product['ID'];
            $morePhotoId = (int)$product['PROPERTY_MORE_PHOTO_VALUE'];
            $morePhotos = $productsInfo[$productId]['morePhoto'] ?? [];

            if ((int)$product['PRICE_GROUP_ID'] === $basePriceId) {
                $basePrices[$productId] = (float)$product['PRICE_VALUE'];
            }
            if ((int)$product['PRICE_GROUP_ID'] === $discountPriceId) {
                $discountPrices[$productId] = (float)$product['PRICE_VALUE'];
            }

            if ($morePhotoId) {
                $morePhotos[$morePhotoId] = $morePhotoId;
            }

            $productsInfo[$productId] = [
                'id' => $productId,
                'active' => $product['ACTIVE'] === 'Y',
                'available' => $product['AVAILABLE'] === 'Y',
                'original_name' => $product['NAME'],
                'detailTemplate' => $product['DETAIL_TEMPLATE'],
                'code' => $product['CODE'],
                'sectionId' => (int)$product['IBLOCK_SECTION_ID'],
                'detailText' => nl2br($product['DETAIL_TEXT']),
                'name' => $product['PROPERTY_NAIMENOVANIE_NA_SAYTE_VALUE'] ?: $product['NAME'],
                'url' => null,
                'price' => null,
                'imageSrc' => null,
                'detailPicture' => $product['DETAIL_PICTURE'],
                'model' => $product['PROPERTY_CML2_ARTICLE_VALUE'],
                'article' => mb_strtolower((string)$product['PROPERTY_CML2_TRAITS_DESCRIPTION']) === 'код'
                    ? $product['PROPERTY_CML2_TRAITS_VALUE'] : '',
                'morePhoto' => $morePhotos,
            ];
        }

        foreach ($productsInfo as $productId => &$product) {
            $base = $basePrices[$productId] ?? 0;
            $discount = $discountPrices[$productId] ?? 0;
            $product['price'] = Price::preparePrice($base, $discount);

            if (!empty($product['morePhoto'])) {
                foreach ($product['morePhoto'] as &$photoId) {
                    $photoId = \CFile::GetPath($photoId);
                }
            }
            $product['imageSrc'] = $product['detailPicture'] ? \CFile::GetPath($product['detailPicture']) : null;

            $product['url'] = \CIBlock::ReplaceDetailUrl($product['detailTemplate'], [
                'ID' => $product['id'],
                'CODE' => $product['code'],
                'IBLOCK_SECTION_ID' => $product['sectionId']
            ], false, 'E');

            unset($product['detailTemplate'], $product['detailPicture']);
        }

        return $productsInfo;
    }

    private static function buildProductQuery(array $productIds, bool $isAvailable): \Bitrix\Main\ORM\Query\Query
    {
        $query = CatalogHelper::addPriceToQuery(
            CatalogHelper::addCatalogToQuery(
                CatalogHelper::getCatalogTableEntity()::query()
            )
        )
            ->setSelect([
                'ID',
                'NAME',
                'CODE',
                'IBLOCK_SECTION_ID',
                'ACTIVE',
                'AVAILABLE' => 'CATALOG.AVAILABLE',
                'PRICE_VALUE' => 'PRICE.PRICE',
                'PRICE_GROUP_ID' => 'PRICE.CATALOG_GROUP_ID',
                'DETAIL_TEXT',
                'DETAIL_PICTURE',
                'PROPERTY_CML2_ARTICLE_VALUE' => 'CML2_ARTICLE.VALUE',
                'PROPERTY_NAIMENOVANIE_NA_SAYTE_VALUE' => 'NAIMENOVANIE_NA_SAYTE.VALUE',
                'DETAIL_TEMPLATE' => 'iblock.DETAIL_PAGE_URL',
                'PROPERTY_MORE_PHOTO_VALUE' => 'MORE_PHOTO.VALUE',
                'PROPERTY_CML2_TRAITS_VALUE' => 'CML2_TRAITS.VALUE',
                'PROPERTY_CML2_TRAITS_DESCRIPTION' => 'CML2_TRAITS.DESCRIPTION',
            ])
            ->whereIn('ID', $productIds)
            ->registerRuntimeField(
                new ExpressionField(
                    'SORT',
                    'FIELD(%s, ' . implode(',', $productIds) . ')',
                    ['ID']
                )
            )
            ->setOrder(['SORT' => 'asc']);

        if ($isAvailable) {
            $query->where('ACTIVE', 'Y');
        }
        return $query;
    }

    /**
     * Получает торговые предложения по id товаров
     *
     * @param array $productsIds
     * @return array
     */
    public static function getOffersByProductsIds(array $productsIds, bool $isAvailable = true): array
    {
        if (empty($productsIds)) {
            return [];
        }

        $offersIdsByProductsIds = self::getOffersIdsByProductsIds($productsIds, $isAvailable);

        $offersIds = collect($offersIdsByProductsIds)
            ->flatten()
            ->toArray();

        $offers = self::getOffersByIds($offersIds);

        return collect($offers)
            ->groupBy('productId')
            ->toArray();
    }

    /**
     * Получает id торговых предложений по id товаров
     *
     * @param $productsIds
     * @return array
     */
    public static function getOffersIdsByProductsIds(array $productsIds, bool $isAvailable = true)
    {
        if (empty($productsIds)) {
            return [];
        }

        $dbResult = CatalogHelper::getOffersTableEntity()::query()
            ->setSelect(['ID', 'PROPERTY_CML2_LINK_VALUE' => 'CML2_LINK.VALUE'])
            ->whereIn('CML2_LINK.VALUE', $productsIds);

        if ($isAvailable) {
            $dbResult = CatalogHelper::addCatalogToQuery($dbResult)
                ->where('CATALOG.AVAILABLE', 'Y')
                ->where('ACTIVE', 'Y');
        }
        $dbResult = $dbResult->exec();

        $offersIdsByProductsIds = [];
        while ($element = $dbResult->fetch()) {
            $offersIdsByProductsIds[(int)$element['PROPERTY_CML2_LINK_VALUE']][] = (int)$element['ID'];
        }

        return $offersIdsByProductsIds;
    }

    public static function getOffersByIds(array $offersIds): array
    {
        if (empty($offersIds)) {
            return [];
        }

        $offers = [];
        $allowedStores = (new StoreRepository())->getAllowedStores();
        $basePriceId = Price::getBasePriceId();
        $discountPriceId = Price::getDiscountPriceId();

        $query = CatalogHelper::addStoreToQuery(
            CatalogHelper::addPriceToQuery(
                CatalogHelper::addCatalogToQuery(
                    CatalogHelper::getOffersTableEntity()::query()
                )
            )
        )->setSelect([
            'ID',
            'ACTIVE',
            'ACTIVE',
            'AVAILABLE' => 'CATALOG.AVAILABLE',
            'QUANTITY' => 'CATALOG.QUANTITY',
            'PRICE_VALUE' => 'PRICE.PRICE',
            'PRICE_GROUP_ID' => 'PRICE.CATALOG_GROUP_ID',
            'PROPERTY_RAZMER_DOP_SVOYSTVA_SPRAVOCHNIKA_KHARAKTERISTIKI_N_VALUE' => 'RAZMER_DOP_SVOYSTVA_SPRAVOCHNIKA_KHARAKTERISTIKI_N.ITEM.VALUE',
            'PROPERTY_TSVET_DOP_SVOYSTVA_SPRAVOCHNIKA_KHARAKTERISTIKI_NO_VALUE' => 'TSVET_DOP_SVOYSTVA_SPRAVOCHNIKA_KHARAKTERISTIKI_NO.ITEM.VALUE',
            'PROPERTY_CML2_LINK_VALUE' => 'CML2_LINK.VALUE',
            'STORE_ID' => 'STORE_PRODUCT.STORE_ID',
            'AMOUNT' => 'STORE_PRODUCT.AMOUNT',
        ])->whereIn('ID', $offersIds)->exec();

        while ($offer = $query->fetch()) {
            $offerId = (int)$offer['ID'];
            $storeId = (int)$offer['STORE_ID'];
            $amount = (int)$offer['AMOUNT'];
            $stores = $offers[$offerId]['stores'] ?? [];
            if ($storeId && $amount) {
                $stores[$storeId] = $amount;
            }

            if ((int)$offer['PRICE_GROUP_ID'] === $basePriceId) {
                $basePrices[$offerId] = (float)$offer['PRICE_VALUE'];
            }
            if ((int)$offer['PRICE_GROUP_ID'] === $discountPriceId) {
                $discountPrices[$offerId] = (float)$offer['PRICE_VALUE'];
            }
            $offers[$offerId] = [
                'id' => $offerId,
                'productId' => (int)$offer['PROPERTY_CML2_LINK_VALUE'],
                'active' => $offer['ACTIVE'] === 'Y',
                'available' => $offer['AVAILABLE'] === 'Y',
                'razmer' => $offer['PROPERTY_RAZMER_DOP_SVOYSTVA_SPRAVOCHNIKA_KHARAKTERISTIKI_N_VALUE'],
                'tsvet' => $offer['PROPERTY_TSVET_DOP_SVOYSTVA_SPRAVOCHNIKA_KHARAKTERISTIKI_NO_VALUE'],
                'price' => null,
                'availableQuantity' => (int)$offer['QUANTITY'],
                'storesAvailability' => $stores,
                'allowedStoresAvailability' => [],
            ];
        }
        foreach ($offers as $offerId => &$offer) {
            $base = $basePrices[$offerId] ?? 0;
            $discount = $discountPrices[$offerId] ?? 0;
            $offer['price'] = Price::preparePrice($base, $discount);
            foreach ($offer['storesAvailability'] as $storeId => $amount) {
                if (isset($allowedStores[$storeId])) {
                    $offer['allowedStoresAvailability'][$storeId] = $amount;
                }
            }
        }

        return $offers;
    }

    /**
     * Получает доступное количество товара
     *
     * @param int $productId
     *
     * @return int
     */
    public static function getAvailableQuantity(int $productId): int
    {
        Loader::includeModule('catalog');
        return \Bitrix\Catalog\ProductTable::query()->setSelect(['QUANTITY'])->where('ID', $productId)->fetch()['QUANTITY'] ?? 0;
    }

    /**
     * Получает ID товаров по ID торговых предложений
     *
     * @param array $offersIds
     *
     * @return array [offerId => productId]
     */
    public static function getProductsIdsByOffersIds(array $offersIds): array
    {
        if (empty($offersIds)) {
            return [];
        }

        $offerProductIdMap = [];

        $dbResult = CatalogHelper::getOffersTableEntity()::query()
            ->setSelect(['ID', 'PROPERTY_CML2_LINK_VALUE' => 'CML2_LINK.VALUE'])
            ->whereIn('ID', $offersIds)
            ->exec();

        while ($offer = $dbResult->fetch()) {
            $offerProductIdMap[(int)$offer['ID']] = (int)$offer['PROPERTY_CML2_LINK_VALUE'];
        }

        return $offerProductIdMap;
    }

    /**
     * применение скидок правил корзины к товарам
     * @param array $products массив товаров или предложений, если товары то к массиву offers так же применяется
     */
    public static function applyDiscounts(array &$products): void
    {
        $productIds = [];
        foreach ($products as $product) {
            $productIds[] = $product['id'];
            if (!empty($product['offers'])) {
                foreach ($product['offers'] as $offer) {
                    $productIds[] = $offer['id'];
                }
            }
        }
        if (!empty($productIds)) {
            $discount = new ProductsDiscount($productIds, [Price::getBasePriceId(), Price::getDiscountPriceId()]);
            foreach ($products as &$product) {
                if (!empty($product['price']) && $finalPrice = $discount->getPriceByProductId($product['id'])) {
                    Price::modifyPrice($product['price'], $finalPrice);
                }
                if (!empty($product['offers'])) {
                    foreach ($product['offers'] as &$offer) {
                        if (!empty($offer['price']) && $finalPrice = $discount->getPriceByProductId($product['id'])) {
                            Price::modifyPrice($offer['price'], $finalPrice);
                        }
                    }
                }
            }
        }
    }

    /**
     * Получает ID просмотренных товаров
     *
     *
     * @see CatalogProductsViewedComponent::getProductIds()
     */
    public static function getViewedProductsIds(int $currentElementId): array
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
            CatalogHelper::getCatalogIblockId(),
            0,
            $basketUserId,
            $currentElementId,
            self::SLIDER_COUNT
        ));
    }


    /**
     * Получает ID «Похожих товаров» (товаров из той же категории)
     *
     * @return array
     */
    public static function getSameProductsIds(int $elementId, int $sectionId, int $cacheTtl = 0): array
    {
        if (!$elementId || !$sectionId) {
            return [];
        }

        $dbResult = CatalogHelper::addCatalogToQuery(CatalogHelper::getCatalogTableEntity()::query())
            ->setSelect(['ID'])
            ->where('ACTIVE', 'Y')
            ->where('CATALOG.AVAILABLE', 'Y')
            ->where('IBLOCK_SECTION_ID', $sectionId)
            ->whereNot('ID', $elementId)
            ->setLimit(self::SLIDER_COUNT);
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

    public static function getNewProductsIds(int $limit = self::SLIDER_COUNT, int $cacheTtl = 0): array
    {
        $date = (new DateTime)->add("-1 months");

        $dbResult = CatalogHelper::addCatalogToQuery(CatalogHelper::getCatalogTableEntity()::query())
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

    public static function getPopularProductsIds(int $limit = self::SLIDER_COUNT, int $cacheTtl = 0): array
    {
        $dbResult = CatalogHelper::addCatalogToQuery(CatalogViewedProductTable::query(), 'PRODUCT_ID')
            ->registerRuntimeField('PRODUCT', [
                'data_type' => CatalogHelper::getCatalogTableEntity(),
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
