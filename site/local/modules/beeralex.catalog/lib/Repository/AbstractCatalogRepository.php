<?php
declare(strict_types=1);
namespace Beeralex\Catalog\Repository;

use Beeralex\Core\Repository\IblockRepository;
use Beeralex\Core\Service\CatalogService;

abstract class AbstractCatalogRepository extends IblockRepository
{
    protected CatalogService $catalogService;

    public function __construct(
        string $iblockCode,
        CatalogService $catalogService
    ) {
        parent::__construct($iblockCode);
        $this->catalogService = $catalogService;
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
        return $this->queryService->fetchGroupedEntities($query);
    }
}
