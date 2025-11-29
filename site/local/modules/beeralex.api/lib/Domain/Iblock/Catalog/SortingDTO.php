<?php

namespace Beeralex\Api\Domain\Iblock\Catalog;

use Beeralex\Core\Http\Resources\Resource;

/** 
 * @property string $currentSortId
 * @property string $defaultSortId
 * @property string $title
 * @property SortingItemDTO[] $availableSorting
 */
class SortingDTO extends Resource
{
    public static function make(array $sorting): static
    {
        return new static([
            'currentSortId' => (string)$sorting['CURRENT_SORT_ID'] ?? '',
            'defaultSortId' => (string)$sorting['DEFAULT_SORT_ID'] ?? '',
            'title' => (string)$sorting['TITLE'] ?? '',
            'availableSorting' => array_map([SortingItemDTO::class, 'make'], $sorting['AVAILABLE_SORTING'] ?? []),
        ]);
    }
}
