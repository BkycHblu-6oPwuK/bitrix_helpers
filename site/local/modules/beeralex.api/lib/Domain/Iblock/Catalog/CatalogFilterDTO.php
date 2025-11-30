<?php

namespace Beeralex\Api\Domain\Iblock\Catalog;

use Beeralex\Core\Http\Resources\Resource;

/** 
 * @property string $filterUrl
 * @property string $clearUrl
 * @property CatalogFilterItemDTO[] $items
 * @property SortingDTO[] $sorting
 * @property array $types
 */
class CatalogFilterDTO extends Resource
{
    /**
     * @param array{filterUrl: string, clearUrl: string, items: array, sorting: mixed, types: array} $catalogFilter
     * @return static
     */
    public static function make(array $catalogFilter): static
    {
        return new static([
            'filterUrl' => (string)$catalogFilter['filterUrl'] ?? '',
            'clearUrl' => (string)$catalogFilter['clearUrl'] ?? '',
            'items' => array_values(array_map([CatalogFilterItemDTO::class, 'make'], $catalogFilter['items'] ?? [])),
            'sorting' => SortingDTO::make($catalogFilter['sorting'] ?? []),
            'types' => $catalogFilter['types'] ?? [],
        ]);
    }
}
