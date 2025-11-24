<?php

namespace Beeralex\Catalog\Repository;

use Beeralex\Catalog\Options;
use Beeralex\Core\Repository\IblockRepository;
use Beeralex\Core\Service\CatalogService;

abstract class AbstractCatalogRepository extends IblockRepository
{
    protected Options $options;
    protected CatalogService $catalogService;

    public function __construct(
        string $iblockCode,
        Options $options,
        CatalogService $catalogService
    ) {
        parent::__construct($iblockCode);
        $this->options = $options;
        $this->catalogService = $catalogService;
    }

    /**
     * Универсальный метод получения элементов каталога с ценами, складами и параметрами товара.
     *
     * @param array $filter Фильтр ORM
     * @param array $select Поля для выборки. Можно использовать алиасы:
     *                      - '*' : все поля элемента инфоблока
     *                      - 'CATALOG' : все поля товара (количество, вес и т.д.)
     *                      - 'PRICE' : подгрузить цены
     *                      - 'STORE_PRODUCT' : подгрузить остатки по складам
     * @param array $order Сортировка
     * @param int|null $limit Лимит
     * @param int|null $offset Смещение
     * @return array
     */
    public function findAll(array $filter = [], array $select = ['*'], array $order = ['SORT' => 'ASC'], ?int $limit = null, ?int $offset = null): array
    {
        $query = $this->query();
        $realSelect = [];

        foreach ($select as $key => $field) {
            if ($field === 'PRICE') {
                $query = $this->catalogService->addPriceToQuery($query);
                $realSelect['PRICE'] = 'PRICE';
                $realSelect['CATALOG_GROUP'] = 'PRICE.CATALOG_GROUP';
                continue;
            }
            if ($field === 'STORE_PRODUCT') {
                $query = $this->catalogService->addStoreToQuery($query);
                $realSelect['STORE_PRODUCT'] = 'STORE_PRODUCT';
                continue;
            }
            if ($field === 'CATALOG') {
                $query = $this->catalogService->addCatalogToQuery($query);
                $realSelect['CATALOG'] = 'CATALOG';
                continue;
            }
            
            if (is_string($key)) {
                $realSelect[$key] = $field;
            } else {
                $realSelect[] = $field;
            }
        }

        if (empty($realSelect)) {
            $realSelect = ['*'];
        }

        $query->setSelect($realSelect);
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
