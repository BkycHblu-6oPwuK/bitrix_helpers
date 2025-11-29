<?php

namespace Beeralex\Api\Domain\Iblock\Catalog;

use Beeralex\Core\Http\Resources\Resource;

/** 
 * @property string $id
 * @property string $name
 * @property bool $base
 * @property int $sort
 * @property string $xmlId
 */
class CatalogPriceGroupDTO extends Resource
{
    public static function make(array $catalogPriceGroup): static
    {
        return new static([
            'id' => $catalogPriceGroup['ID'] ?? '',
            'name' => $catalogPriceGroup['NAME'] ?? '',
            'base' => $catalogPriceGroup['BASE'] ?? false,
            'sort' => $catalogPriceGroup['SORT'] ?? 0,
            'xmlId' => $catalogPriceGroup['XML_ID'] ?? '',
        ]);
    }
}
