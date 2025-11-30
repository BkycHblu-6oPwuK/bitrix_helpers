<?php

namespace Beeralex\Api\Domain\Iblock;

use Beeralex\Core\Http\Resources\Resource;

/** 
 * @property string $id
 * @property string $name
 * @property string $code
 * @property string $url
 * @property string $picture_src
 */
class SectionDTO extends Resource
{
    public static function make(array $sectionItem): static
    {
        return new static([
            'id' => $sectionItem['ID'] ?? '',
            'name' => $sectionItem['NAME'] ?? '',
            'code' => $sectionItem['CODE'] ?? '',
            'url' => $sectionItem['URL'] ?? '',
            'pictureSrc' => $sectionItem['PICTURE_SRC'] ?? '',
        ]);
    }
}
