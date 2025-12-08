<?php

namespace Beeralex\Api\Domain\Iblock\Catalog;

use Beeralex\Core\Http\Resources\Resource;

/** 
 * @property int $id
 * @property string $name
 * @property string $pictureSrc
 * @property string $value
 */
class OffersTreePropsValuesItemDTO extends Resource
{
    public static function make(array $item): static
    {
        return new static([
            'id' => $item['ID'] ?? 0,
            'name' => $item['NAME'] ?? '',
            'pictureSrc' => $item['PICTURE_SRC'] ?? '',
            'value' => $item['VALUE'] ?? '',
        ]);
    }
}
