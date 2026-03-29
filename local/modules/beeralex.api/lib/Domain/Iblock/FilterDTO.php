<?php

namespace Beeralex\Api\Domain\Iblock;

use Beeralex\Core\Http\Resources\Resource;

/** 
 * @property string $filterUrl
 * @property string $clearUrl
 * @property FilterItemDTO[] $items
 * @property SortingDTO[] $sorting
 * @property array $types
 * DTO для фильтра catalog.smart.filter
 */
class FilterDTO extends Resource
{
    /**
     * @param array{filterUrl: string, clearUrl: string, items: array, sorting: mixed, types: array} $catalogFilter
     * @return static
     */
    public static function make(array $filter): static
    {
        return new static([
            'filterUrl' => (string)$filter['filterUrl'] ?? '',
            'clearUrl' => (string)$filter['clearUrl'] ?? '',
            'items' => array_values(array_map([FilterItemDTO::class, 'make'], $filter['items'] ?? [])),
            'sorting' => SortingDTO::make($filter['sorting'] ?? []),
            'types' => $filter['types'] ?? [],
        ]);
    }
}
