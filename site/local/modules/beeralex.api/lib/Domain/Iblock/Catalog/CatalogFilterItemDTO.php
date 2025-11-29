<?php

namespace Beeralex\Api\Domain\Iblock\Catalog;

use Beeralex\Core\Http\Resources\Resource;
/** 
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $propertyType
 * @property string $userType
 * @property string $displayType
 * @property bool $displayExpanded
 * @property CatalogFilterValueItemDTO[] $values
 */
class CatalogFilterItemDTO extends Resource
{
    public static function make(array $filterItem): static
    {
        return new static([
            'id' => (int)$filterItem['ID'] ?? 0,
            'code' => $filterItem['CODE'] ?? '',
            'name' => $filterItem['NAME'] ?? '',
            'propertyType' => $filterItem['PROPERTY_TYPE'] ?? '',
            'userType' => $filterItem['USER_TYPE'] ?? '',
            'displayType' => $filterItem['DISPLAY_TYPE'] ?? '',
            'displayExpanded' => $filterItem['DISPLAY_EXPANDED'] ?? '',
            'values' => array_map([CatalogFilterValueItemDTO::class, 'make'], $filterItem['VALUES'] ?? []),
        ]);
    }
}
