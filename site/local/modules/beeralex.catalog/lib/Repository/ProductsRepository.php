<?php

declare(strict_types=1);

namespace Beeralex\Catalog\Repository;

use Beeralex\Catalog\Contracts\ProductRepositoryContract;
use Beeralex\Core\Service\CatalogService;

class ProductsRepository extends AbstractCatalogRepository implements ProductRepositoryContract
{
    public function __construct(
        string $iblockCode,
        CatalogService $catalogService,
        protected readonly CatalogViewedProductRepository $catalogViewedProductRepository
    ) {
        parent::__construct($iblockCode, $catalogService);
    }

    /**
     * Получает товары по ID со связкой цен.
     */
    public function getProducts(array $productIds, bool $onlyActive = true): array
    {
        if (empty($productIds)) {
            return [];
        }

        $filter = ['ID' => $productIds];
        if ($onlyActive) {
            $filter['ACTIVE'] = 'Y';
        }

        $items = $this->findAll(
            $filter,
        );

        $products = [];
        foreach ($items as $item) {
            $products[(int)$item['ID']] = $item;
        }

        $result = [];
        foreach ($productIds as $id) {
            if (isset($products[$id])) {
                $result[] = $products[$id];
            }
        }

        return $result;
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

    /**
     * Получает ID похожих товаров из той же секции.
     */
    public function getSameProductsIds(int $elementId, int $sectionId, int $limit = 15, int $cacheTtl = 0): array
    {
        if (!$elementId || !$sectionId) {
            return [];
        }

        $dbResult = $this->catalogService->addCatalogToQuery($this->query())
            ->setSelect(['ID'])
            ->where('ACTIVE', 'Y')
            ->where('CATALOG.AVAILABLE', 'Y')
            ->where('IBLOCK_SECTION_ID', $sectionId)
            ->whereNot('ID', $elementId)
            ->setLimit($limit);

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

    /**
     * Получает ID новых товаров (добавленных за последний месяц).
     */
    public function getNewProductsIds(int $limit = 15, int $cacheTtl = 0, int $countMonts = 1): array
    {
        $date = (new \Bitrix\Main\Type\DateTime())->add("-{$countMonts} months");

        $dbResult = $this->catalogService->addCatalogToQuery($this->query())
            ->setSelect(['ID'])
            ->where('ACTIVE', 'Y')
            ->where('CATALOG.AVAILABLE', 'Y')
            ->where('DATE_CREATE', '>=', $date)
            ->setLimit($limit);

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

    /**
     * Получает ID популярных товаров на основе просмотров.
     */
    public function getPopularProductsIds(int $limit = 15, int $cacheTtl = 0): array
    {
        $dbResult = $this->catalogService->addCatalogToQuery(
            $this->catalogViewedProductRepository->query(),
            'PRODUCT_ID'
        )
            ->registerRuntimeField('PRODUCT', [
                'data_type' => $this->entityClass,
                'reference' => ['=this.PRODUCT_ID' => 'ref.ID'],
                'join_type' => 'INNER',
            ])
            ->setSelect([
                'PRODUCT_ID',
                'VIEWS' => new \Bitrix\Main\ORM\Fields\ExpressionField('VIEWS', 'COUNT(*)'),
            ])
            ->where('PRODUCT.ACTIVE', 'Y')
            ->where('CATALOG.AVAILABLE', 'Y')
            ->setGroup('PRODUCT_ID')
            ->setOrder(['VIEWS' => 'DESC'])
            ->setLimit($limit);

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

        return $this->catalogViewedProductRepository->getViewedProductIds(
            $this->entityId,
            $basketUserId,
            $currentElementId
        );
    }

    public function getProductWithOffers(int $productId): ?array
    {
        $product = $this->one(['ID' => $productId]);
        if (!$product) {
            return null;
        }

        $offers = service(\Beeralex\Catalog\Contracts\OfferRepositoryContract::class)->getOffersByProductIds([$productId]);
        $product['OFFERS'] = $offers[$productId] ?? [];

        return $product;
    }
}
