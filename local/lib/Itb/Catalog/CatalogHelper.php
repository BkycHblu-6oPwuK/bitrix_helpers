<?php
namespace Itb\Catalog;

use Bitrix\Main\Loader;
use Bitrix\Main\ORM\Query\Query;
use Itb\Core\Helpers\IblockHelper;
use Itb\User\Enum\Gender;

class CatalogHelper
{
    /**
     * @return int ИД инфоблока каталога товаров
     */
    public static function getCatalogIblockId(): int
    {
        return IblockHelper::getIblockIdByCode('catalog');
    }

    public static function getCatalogTableEntity()
    {
        return IblockHelper::getElementApiTableByCode('catalog');
    }

    public static function getOffersIblockId(): int
    {
        return IblockHelper::getIblockIdByCode('offers');
    }

    public static function getOffersTableEntity()
    {
        return IblockHelper::getElementApiTableByCode('offers');
    }

    public static function getCalogSectionsEntity()
    {
        static $entity = null;
        if (null === $entity) {
            $entity = \Itb\Iblock\Model\SectionModel::compileEntityByIblock(static::getCatalogIblockId());
        }
        return $entity;
    }

    public static function getProdQuantity($ID)
    {
        if (\Bitrix\Main\Loader::IncludeModule("iblock")) {
            $ar_res = \CCatalogProduct::GetByID($ID);
            if ($ar_res['QUANTITY'] > 0) {
                return 'Y';
            } else {
                return 'N';
            }
        }
    }

    /**
     * Добавить каталог в запрос - CATALOG
     */
    public static function addCatalogToQuery(Query $query, string $thisFieldReference = 'ID'): Query
    {
        Loader::includeModule('catalog');
        $query->registerRuntimeField('CATALOG', [
            'data_type' => \Bitrix\Catalog\ProductTable::class,
            'reference' => [
                "=this.{$thisFieldReference}" => 'ref.ID',
            ],
            'join_type' => 'LEFT'
        ]);
        return $query;
    }
    /**
     * Добавить цены в запрос - PRICE
     */
    public static function addPriceToQuery(Query $query, string $thisFieldReference = 'ID'): Query
    {
        Loader::includeModule('catalog');
        $query->registerRuntimeField('PRICE', [
            'data_type' => \Bitrix\Catalog\PriceTable::class,
            'reference' => [
                "=this.{$thisFieldReference}" => 'ref.PRODUCT_ID',
            ],
            'join_type' => 'LEFT'
        ]);
        return $query;
    }
    /**
     * Добавить склад с продуктами в запрос - STORE_PRODUCT
     */
    public static function addStoreToQuery(Query $query, string $thisFieldReference = 'ID'): Query
    {
        Loader::includeModule('catalog');
        $query->registerRuntimeField('STORE_PRODUCT', [
            'data_type' => \Bitrix\Catalog\StoreProductTable::class,
            'reference' => [
                "=this.{$thisFieldReference}" => 'ref.PRODUCT_ID',
            ],
            'join_type' => 'LEFT'
        ]);
        return $query;
    }
}