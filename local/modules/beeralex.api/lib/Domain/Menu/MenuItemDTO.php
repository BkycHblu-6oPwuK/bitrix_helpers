<?php
namespace Beeralex\Api\Domain\Menu;

use Beeralex\Core\Http\Resources\Resource;

/** 
 * @property int $id
 * @property string $name
 * @property string $code
 * @property int $iblockSectionId
 * @property string $sectionPageUrl
 * @property string $link
 * @property MenuItemDTO[] $children
*/
class MenuItemDTO extends Resource 
{
    public static function make(array $item): static
    {
        return new static([
            'id' => (int)($item['ID'] ?? 0),
            'name' => $item['NAME'] ?? '',
            'code' => $item['CODE'] ?? '',
            'iblockSectionId' => (int)($item['IBLOCK_SECTION_ID'] ?? 0),
            'link' => $item['LINK'] ?? '',
            'children' => array_map([static::class, 'make'], $item['CHILDREN'] ?? []),
        ]);
    }
}