<?php

namespace Beeralex\Catalog\Service;

use Beeralex\Catalog\Contracts\OfferRepositoryContract;
use Beeralex\Catalog\Contracts\ProductRepositoryContract;
use Beeralex\Catalog\Discount\ProductsDiscount;
use Beeralex\Catalog\Repository\CatalogViewedProductRepository;
use Beeralex\Catalog\Repository\PriceTypeRepository;
use Bitrix\Main\Loader;

class CatalogService
{
    public function __construct(
        protected readonly ProductRepositoryContract $productsRepository,
        protected readonly OfferRepositoryContract $offersRepository,
        protected readonly CatalogViewedProductRepository $viewedProductRepository,
    ) {}

    /**
     * Возвращает товары с их предложениями (и опционально скидками).
     */
    public function getProductsWithOffers(array $productIds, bool $isAvailable = true, bool $applyDiscounts = true): array
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

    /**
     * Приводит цены товара в соответствие с рассчитанными скидками.
     */
    protected function updateProductPrices(array &$product, float $discountedPrice): void
    {
        $basePriceValue = 0.0;
        $currentMinPrice = PHP_FLOAT_MAX;
        $basePriceId = service(PriceTypeRepository::class)->getBasePriceId();

        // Ищем базовую цену и текущую минимальную в сырых данных
        // PRICE - это массив цен из БД
        $prices = $product['PRICE'] ?? [];
        if (!is_array($prices)) {
            $prices = [];
        }

        // Если пришла одна цена (ассоциативный массив), оборачиваем её в список
        if (isset($prices['PRICE']) || (isset($prices['ID']) && !isset($prices[0]))) {
            $prices = [$prices];
            // Обновляем структуру в продукте, чтобы она была единообразной (список)
            $product['PRICE'] = $prices;
        }

        foreach ($prices as $price) {
            $priceValue = (float)($price['PRICE'] ?? 0.0);
            $groupId = (int)($price['CATALOG_GROUP_ID'] ?? 0);

            if ($groupId === $basePriceId) {
                $basePriceValue = $priceValue;
            }
            
            if ($priceValue < $currentMinPrice) {
                $currentMinPrice = $priceValue;
            }
        }

        // Если базовой цены нет, считаем скидочную цену базой (скидка 0%)
        if ($basePriceValue <= 0) {
            $basePriceValue = $discountedPrice;
        }

        // Если рассчитанная цена ниже текущей минимальной, добавляем её как новую цену
        if ($discountedPrice < $currentMinPrice) {
            // Добавляем динамическую цену в массив PRICE
            // Формируем структуру, похожую на ту, что приходит из БД
            $product['PRICE'][] = [
                'ID' => 0, // Маркер динамической цены
                'PRODUCT_ID' => $product['ID'],
                'EXTRA_ID' => 0,
                'CATALOG_GROUP_ID' => 0, // ID группы для динамической цены
                'PRICE' => $discountedPrice,
                'CURRENCY' => 'RUB', // TODO: брать валюту из настроек или базовой цены
                'TIMESTAMP_X' => new \Bitrix\Main\Type\DateTime(),
                'QUANTITY_FROM' => 0,
                'QUANTITY_TO' => 0,
                'TMP_ID' => '',
                'PRICE_SCALE' => $discountedPrice,
                'CATALOG_GROUP' => [
                    'ID' => 0,
                    'NAME' => 'Discount Price',
                    'BASE' => 'N',
                    'SORT' => 100,
                    'XML_ID' => 'discount_price',
                ]
            ];
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

        // Передаем только базовую цену для расчета скидок, 
        // так как DiscountPriceId больше нет, а скидки обычно считаются от базы.
        $discount = new ProductsDiscount($ids, [
            service(PriceTypeRepository::class)->getBasePriceId(),
        ]);

        foreach ($products as &$product) {
            if ($price = $discount->getPriceByProductId($product['ID'])) {
                $this->updateProductPrices($product, $price);
            }

            foreach ($product['OFFERS'] ?? [] as &$offer) {
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

        return $this->viewedProductRepository->getViewedProductIds(
            $this->productsRepository->entityId,
            $basketUserId,
            $currentElementId
        );
    }
}
