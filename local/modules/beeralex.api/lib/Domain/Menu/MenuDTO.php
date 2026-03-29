<?php
namespace Beeralex\Api\Domain\Menu;

use Beeralex\Core\Http\Resources\Resource;

/** 
 * @property MenuItemDTO[] $menu
*/
class MenuDTO extends Resource 
{
    public static function make(array $data): static
    {
        return new static([
            'menu' => array_map([MenuItemDTO::class, 'make'], $data['MENU'] ?? []),
        ]);
    }
}