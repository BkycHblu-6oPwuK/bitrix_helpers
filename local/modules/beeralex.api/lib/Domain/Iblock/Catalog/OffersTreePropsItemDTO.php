<?php

namespace Beeralex\Api\Domain\Iblock\Catalog;

use Beeralex\Core\Http\Resources\Resource;

/** 
 * @property string $code
 * @property string $name
 * @property OffersTreePropsValuesItemDTO[] $values
 */
class OffersTreePropsItemDTO extends Resource
{
    public static function make(array $item): static
    {
        return new static([
            'code' => $item['CODE'] ?? '',
            'name' => $item['NAME'] ?? '',
            'values' => array_map([OffersTreePropsValuesItemDTO::class, 'make'], $item['VALUES'] ?? []),
        ]);
    }
}
