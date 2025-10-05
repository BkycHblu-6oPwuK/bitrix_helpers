<?php

namespace App\Catalog\Helper;

use Bitrix\Main\Loader;
use Bitrix\Catalog\CatalogIblockTable;
use Bitrix\Main\ORM\Query\Query;

class CatalogHelper
{
    /**
     * Карта каталогов: ключ — CATALOG_IBLOCK_ID
     *
     * Формат:
     * [
     *   2 => ['CATALOG_IBLOCK_ID' => 2, 'OFFERS_IBLOCK_ID' => 3|null, 'SKU_PROPERTY_ID' => 45|null],
     *   4 => ['CATALOG_IBLOCK_ID' => 4, 'OFFERS_IBLOCK_ID' => null, 'SKU_PROPERTY_ID' => null],
     * ]
     *
     * @var array<int, array{CATALOG_IBLOCK_ID:int, OFFERS_IBLOCK_ID:?int, SKU_PROPERTY_ID:?int}|null>
     */
    protected static ?array $catalogMap = null;

    /**
     * Собирает карту всех каталогов (ключ — CATALOG_IBLOCK_ID).
     * Обрабатывает записи, где PRODUCT_IBLOCK_ID == 0 (каталог) и PRODUCT_IBLOCK_ID > 0 (offers).
     *
     * @return array<int, array{CATALOG_IBLOCK_ID:int, OFFERS_IBLOCK_ID:?int, SKU_PROPERTY_ID:?int}>
     */
    public static function getCatalogMap(): array
    {
        if (self::$catalogMap !== null) {
            return self::$catalogMap;
        }

        if (!Loader::includeModule('catalog')) {
            self::$catalogMap = [];
            return self::$catalogMap;
        }

        $map = [];
        $res = CatalogIblockTable::query()->setSelect(['IBLOCK_ID', 'PRODUCT_IBLOCK_ID', 'SKU_PROPERTY_ID', 'CODE' => 'IBLOCK.CODE'])->setCacheTtl(86400)->cacheJoins(true)->exec();

        while ($row = $res->fetch()) {
            $iblockId = (int)($row['IBLOCK_ID'] ?? 0);
            if ($iblockId <= 0) {
                continue;
            }

            $productIblock = (int)($row['PRODUCT_IBLOCK_ID'] ?? 0);
            $skuProp = (int)($row['SKU_PROPERTY_ID'] ?? 0);
            $code = $row['CODE'] ?? null;

            // case 1: офферы (SKU)
            if ($productIblock > 0) {
                $catalogId = $productIblock;
                $offersId = $iblockId;
                $skuPropId = $skuProp > 0 ? $skuProp : null;

                if (!isset($map[$catalogId])) {
                    $map[$catalogId] = [
                        'CATALOG_IBLOCK_ID'   => $catalogId,
                        'CATALOG_IBLOCK_CODE' => null,
                        'OFFERS_IBLOCK_ID'    => $offersId,
                        'OFFERS_IBLOCK_CODE'  => $code,
                        'SKU_PROPERTY_ID'     => $skuPropId,
                    ];
                } else {
                    if (empty($map[$catalogId]['OFFERS_IBLOCK_ID'])) {
                        $map[$catalogId]['OFFERS_IBLOCK_ID'] = $offersId;
                        $map[$catalogId]['OFFERS_IBLOCK_CODE'] = $code;
                    }
                    if ($map[$catalogId]['SKU_PROPERTY_ID'] === null && $skuPropId !== null) {
                        $map[$catalogId]['SKU_PROPERTY_ID'] = $skuPropId;
                    }
                }

                continue;
            }

            // case 2: сам каталог
            $catalogId = $iblockId;
            if (!isset($map[$catalogId])) {
                $map[$catalogId] = [
                    'CATALOG_IBLOCK_ID'   => $catalogId,
                    'CATALOG_IBLOCK_CODE' => $code,
                    'OFFERS_IBLOCK_ID'    => null,
                    'OFFERS_IBLOCK_CODE'  => null,
                    'SKU_PROPERTY_ID'     => $skuProp > 0 ? $skuProp : null,
                ];
            } else {
                if (empty($map[$catalogId]['CATALOG_IBLOCK_CODE'])) {
                    $map[$catalogId]['CATALOG_IBLOCK_CODE'] = $code;
                }
                if ($map[$catalogId]['SKU_PROPERTY_ID'] === null && $skuProp > 0) {
                    $map[$catalogId]['SKU_PROPERTY_ID'] = $skuProp;
                }
            }
        }

        self::$catalogMap = $map;
        return self::$catalogMap;
    }


    /**
     * Возвращает пару "CATALOG ↔ OFFERS" для заданного iblockId.
     * Если передан iblock каталога — возвращает его запись.
     * Если передан iblock offers — ищет запись по значению OFFERS_IBLOCK_ID.
     * Если не найдено — возвращает дефолт (предполагаем, что это каталог без офферов).
     *
     * @param int $iblockId
     * @return array{CATALOG_IBLOCK_ID:int, OFFERS_IBLOCK_ID:?int, SKU_PROPERTY_ID:?int}
     */
    public static function getCatalogRelationByIblockId(int $iblockId): array
    {
        $map = self::getCatalogMap();

        // 1) если это ключ (каталог) — вернём напрямую
        if (isset($map[$iblockId])) {
            return $map[$iblockId];
        }

        // 2) иначе ищем как offers (по значению OFFERS_IBLOCK_ID)
        foreach ($map as $catalogRelation) {
            if (!empty($catalogRelation['OFFERS_IBLOCK_ID']) && $catalogRelation['OFFERS_IBLOCK_ID'] === $iblockId) {
                return $catalogRelation;
            }
        }

        // 3) дефолт: предполагаем, что переданный iblock — каталог без offers
        return [
            'CATALOG_IBLOCK_ID' => $iblockId,
            'OFFERS_IBLOCK_ID'  => null,
            'SKU_PROPERTY_ID'   => null,
        ];
    }

    /**
     * Вернуть связанный инфоблок:
     * - если передан каталог -> вернуть offers id (или null)
     * - если передан offers -> вернуть catalog id
     */
    public static function getLinkedIblockId(int $iblockId): ?int
    {
        $rel = self::getCatalogRelationByIblockId($iblockId);

        if ($rel['CATALOG_IBLOCK_ID'] === $iblockId) {
            return $rel['OFFERS_IBLOCK_ID'];
        }

        if ($rel['OFFERS_IBLOCK_ID'] === $iblockId) {
            return $rel['CATALOG_IBLOCK_ID'];
        }

        return null;
    }

    /**
     * Добавить связь каталога (CATALOG) в запрос
     */
    public static function addCatalogToQuery(Query $query, string $thisFieldReference = 'ID'): Query
    {
        if (!Loader::includeModule('catalog')) {
            return $query;
        }

        $query->registerRuntimeField('CATALOG', [
            'data_type'  => \Bitrix\Catalog\ProductTable::class,
            'reference'  => ["=this.{$thisFieldReference}" => 'ref.ID'],
            'join_type'  => 'LEFT',
        ]);

        return $query;
    }

    /**
     * Добавить цены (PRICE) в запрос
     */
    public static function addPriceToQuery(Query $query, string $thisFieldReference = 'ID'): Query
    {
        if (!Loader::includeModule('catalog')) {
            return $query;
        }

        $query->registerRuntimeField('PRICE', [
            'data_type' => \Bitrix\Catalog\PriceTable::class,
            'reference' => ["=this.{$thisFieldReference}" => 'ref.PRODUCT_ID'],
            'join_type' => 'LEFT',
        ]);

        return $query;
    }

    /**
     * Добавить склад/количество (STORE_PRODUCT) в запрос
     */
    public static function addStoreToQuery(Query $query, string $thisFieldReference = 'ID'): Query
    {
        if (!Loader::includeModule('catalog')) {
            return $query;
        }

        $query->registerRuntimeField('STORE_PRODUCT', [
            'data_type' => \Bitrix\Catalog\StoreProductTable::class,
            'reference' => ["=this.{$thisFieldReference}" => 'ref.PRODUCT_ID'],
            'join_type' => 'LEFT',
        ]);

        return $query;
    }
}
