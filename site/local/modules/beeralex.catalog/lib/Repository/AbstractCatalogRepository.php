<?php
declare(strict_types=1);
namespace Beeralex\Catalog\Repository;

use Beeralex\Core\Repository\IblockRepository;
use Beeralex\Core\Service\CatalogService;
use Beeralex\Core\Service\UrlService;

abstract class AbstractCatalogRepository extends IblockRepository
{
    public function __construct(
        string $iblockCode,
        protected readonly CatalogService $catalogService,
        protected readonly UrlService $urlService
    ) {
        parent::__construct($iblockCode);
    }

    /**
     * Универсальный метод получения элементов каталога с ценами, складами и параметрами товара.
     *
     * @param array $filter Фильтр ORM
     * @param array $select Поля для выборки. Можно использовать алиасы:
     *                      - '*' : все поля элемента и runtime-поля
     *                      - 'CATALOG' : все поля товара (количество, вес и т.д.)
     *                      - 'PRICE' : подгрузить цены
     *                      - 'PRICE.CATALOG_GROUP' : подгрузить группы цен
     *                      - 'STORE_PRODUCT' : подгрузить остатки по складам
     * @param array $order Сортировка
     * @param int|null $limit Лимит
     * @param int|null $offset Смещение
     * @return array
     */
    public function findAll(array $filter = [], array $select = ['*'], array $order = ['SORT' => 'ASC'], ?int $limit = null, ?int $offset = null): array
    {
        if (empty($select)) {
            $select = ['*'];
        }
        if ($select === ['*']) {
            $select = ['*', 'PRICE', 'PRICE.CATALOG_GROUP', 'STORE_PRODUCT', 'CATALOG'];
        }
        $select = array_merge($select, ['IBLOCK.DETAIL_PAGE_URL', 'CODE', 'ID', 'IBLOCK_SECTION_ID']);
        $query = $this->query();
        $priceAdded = false;
        $priceCatalogAdded = false;
        $storeAdded = false;
        $catalogAdded = false;

        foreach ($select as $field) {
            if ($priceAdded === false && ($field === 'PRICE' || strstr($field, 'PRICE.'))) {
                $query = $this->catalogService->addPriceToQuery($query);
                $priceAdded = true;
            } elseif ($priceCatalogAdded === false && ($field === 'PRICE.CATALOG_GROUP' || strstr($field, 'PRICE.CATALOG_GROUP.'))) {
                $query = $this->catalogService->addPriceToQuery($query);
                $priceCatalogAdded = true;
            } elseif ($storeAdded === false && ($field === 'STORE_PRODUCT' || strstr($field, 'STORE_PRODUCT.'))) {
                $query = $this->catalogService->addStoreToQuery($query);
                $storeAdded = true;
            } elseif ($catalogAdded === false && ($field === 'CATALOG' || strstr($field, 'CATALOG.'))) {
                $query = $this->catalogService->addCatalogToQuery($query);
                $catalogAdded = true;
            }
            if($priceAdded && $priceCatalogAdded && $storeAdded && $catalogAdded) {
                break;
            }
        }

        $query->setSelect($select);
        $query->setFilter($filter);
        $query->setOrder($order);

        if ($limit) {
            $query->setLimit($limit);
        }
        if ($offset) {
            $query->setOffset($offset);
        }
        $result = $this->queryService->fetchGroupedEntities($query);

        foreach ($result as &$item) {
            if (isset($item['PRICE']) && isset($item['PRICE']['ID'])) {
                $item['PRICE'] = [$item['PRICE']];
            }
            if(isset($item['STORE_PRODUCT']) && isset($item['STORE_PRODUCT']['ID'])) {
                $item['STORE_PRODUCT'] = [$item['STORE_PRODUCT']];
            }
            if(isset($item['IBLOCK']['DETAIL_PAGE_URL']) && $item['IBLOCK']['DETAIL_PAGE_URL'] !== '#PRODUCT_URL#') {
                $item['DETAIL_PAGE_URL'] = $this->urlService->getDetailUrl([
                    'CODE' => $item['CODE'],
                    'ID' => $item['ID'],
                    'IBLOCK_SECTION_ID' => $item['IBLOCK_SECTION_ID'],
                ], $item['IBLOCK']['DETAIL_PAGE_URL'])['clean_url'];
            }
        }
        return $result;
    }
}
