<?php

namespace Beeralex\Api\Domain\Iblock;

use Beeralex\Core\Http\Resources\Resource;

/** 
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int $sort
 * @property bool $default
 * @property string $direction
 * @property string $sortBy
 */
class SortingItemDTO extends Resource
{
    public static function make(array $sortingItem): static
    {
        return new static([
            'id' => (int)$sortingItem['ID'] ?? '',
            'name' => (string)$sortingItem['NAME'] ?? '',
            'code' => (string)$sortingItem['CODE'] ?? '',
            'sort' => (int)$sortingItem['SORT'] ?? 0,
            'default' => $sortingItem['DEFAULT']['ITEM']['XML_ID'] === 'Y' ?? false,
            'direction' => $sortingItem['DIRECTION']['VALUE'] ?? '',
            'sortBy' => $sortingItem['SORT_BY']['VALUE'] ?? '',
        ]);
    }
}
