<?php
namespace Beeralex\Catalog\Repository;

use Beeralex\Core\Repository\Repository;
use Bitrix\Catalog\CatalogViewedProductTable;
use Bitrix\Main\Loader;

class CatalogViewedProductRepository extends Repository
{
    public function __construct()
    {
        Loader::includeModule('catalog');
        parent::__construct(CatalogViewedProductTable::class);
    }

    /**
     * Получает ID просмотренных товаров для пользователя.
     */
    public function getViewedProductIds(int $iblockId, int $userId, int $currentElementId, int $limit = 15): array
    {
        return array_values(CatalogViewedProductTable::getProductSkuMap(
            $iblockId,
            0,
            $userId,
            $currentElementId,
            $limit
        ));
    }
}
