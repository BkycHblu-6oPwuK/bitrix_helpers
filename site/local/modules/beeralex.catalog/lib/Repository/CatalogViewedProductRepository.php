<?php
declare(strict_types=1);
namespace Beeralex\Catalog\Repository;

use Beeralex\Core\Repository\Repository;
use Bitrix\Catalog\CatalogViewedProductTable;
use Bitrix\Main\Loader;

/**
 * @property CatalogViewedProductTable $entityClass
 */
class CatalogViewedProductRepository extends Repository
{
    public function __construct()
    {
        parent::__construct(CatalogViewedProductTable::class);
    }

    /**
     * Получает ID просмотренных товаров для пользователя.
     */
    public function getViewedProductIds(int $iblockId, int $userId, int $currentElementId, int $limit = 15): array
    {
        return array_values($this->entityClass->getProductSkuMap(
            $iblockId,
            0,
            $userId,
            $currentElementId,
            $limit
        ));
    }
}
