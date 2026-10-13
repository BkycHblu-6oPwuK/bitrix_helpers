<?php

namespace Beeralex\Api\Domain\Iblock;

use Beeralex\Core\Http\Resources\Resource;

/** 
 * @property string $filterUrl
 * @property string $clearUrl
 * @property string $sectionUrl
 * @property FilterItemDTO[] $items
 * @property SortingDTO[]|null $sorting
 * @property array $types
 * DTO для фильтра catalog.smart.filter
 */
class FilterDTO extends Resource
{
    /**
     * @param array{filterUrl: string, clearUrl: string, sectionUrl: string, items: array, sorting: mixed, types: array} $filter
     * @return static
     */
    public static function make(array $filter): static
    {
        return new static([
            'filterUrl' => (string)$filter['filterUrl'] ?? '',
            'clearUrl' => (string)$filter['clearUrl'] ?? '',
            'sectionUrl' => (string)$filter['sectionUrl'] ?? '',
            'items' => array_values(array_map([FilterItemDTO::class, 'make'], $filter['items'] ?? [])),
            'sorting' => SortingDTO::make($filter['sorting'] ?? []),
            'types' => $filter['types'] ?? [],
        ]);
    }
}
