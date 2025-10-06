<?php

namespace App\Catalog\Repository;

use App\Catalog\Contracts\ProductRepositoryContract;
use Beeralex\Core\Repository\BaseIblockRepository;
use App\Catalog\Helper\CatalogHelper;
use App\Catalog\Helper\PriceHelper;
use Bitrix\Main\ORM\Fields\ExpressionField;

class ProductsRepository extends BaseIblockRepository implements ProductRepositoryContract
{
    public function __construct()
    {
        parent::__construct('catalog');
    }

    /**
     * Получает товары по ID со связкой цен.
     */
    public function getProducts(array $productIds, bool $onlyActive = true): array
    {
        if (empty($productIds)) {
            return [];
        }

        $basePriceId = PriceHelper::getBasePriceId();
        $discountPriceId = PriceHelper::getDiscountPriceId();

        $query = $this->buildProductQuery($productIds, $onlyActive);
        $result = $query->exec();

        $products = [];
        $basePrices = [];
        $discountPrices = [];

        while ($row = $result->fetch()) {
            $id = (int)$row['ID'];

            $products[$id] ??= [
                'id' => $id,
                'active' => $row['ACTIVE'] === 'Y',
                'available' => $row['AVAILABLE'] === 'Y',
                'name' => $row['NAME'],
                'code' => $row['CODE'],
                'sectionId' => (int)$row['IBLOCK_SECTION_ID'],
                'url' => null,
                'price' => null,
                'imageSrc' => null,
                'morePhoto' => [],
            ];

            if ((int)$row['PRICE_GROUP_ID'] === $basePriceId) {
                $basePrices[$id] = (float)$row['PRICE_VALUE'];
            }

            if ((int)$row['PRICE_GROUP_ID'] === $discountPriceId) {
                $discountPrices[$id] = (float)$row['PRICE_VALUE'];
            }
        }

        foreach ($products as $id => &$product) {
            $base = $basePrices[$id] ?? 0.0;
            $discount = $discountPrices[$id] ?? 0.0;
            $product['price'] = PriceHelper::preparePrice($base, $discount);
        }

        return $products;
    }

    /**
     * Строит ORM-запрос для получения товаров и цен.
     */
    private function buildProductQuery(array $ids, bool $onlyActive)
    {
        $query = CatalogHelper::addPriceToQuery(
            CatalogHelper::addCatalogToQuery(
                $this->query()
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
            ])
            ->whereIn('ID', $ids)
            ->registerRuntimeField(
                new ExpressionField(
                    'SORT',
                    'FIELD(%s, ' . implode(',', $ids) . ')',
                    ['ID']
                )
            )
            ->setOrder(['SORT' => 'asc']);

        if ($onlyActive) {
            $query->where('ACTIVE', 'Y');
        }

        return $query;
    }

    /**
     * Возвращает список ID активных и доступных товаров.
     */
    public function getAvailableProductIds(array $filter = []): array
    {
        $query = $this->query()
            ->setSelect(['ID'])
            ->setFilter(array_merge(['ACTIVE' => 'Y'], $filter));

        $ids = [];
        $res = $query->exec();
        while ($row = $res->fetch()) {
            $ids[] = (int)$row['ID'];
        }

        return $ids;
    }
}
