<?php

namespace Beeralex\Api\Domain\Iblock\Catalog;

use Beeralex\Api\Domain\Iblock\SectionDTO;
use Beeralex\Core\Http\Resources\Resource;

/** 
 * @property CatalogItemDTO[] $products
 * @property SectionDTO[] $sections
 * DTO для результата поиска по каталогу
 */
class CatalogSearchDTO extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'products' => array_map([CatalogItemDTO::class, 'make'], $data['PRODUCTS'] ?? []),
            'sections' => array_map([SectionDTO::class, 'make'], $data['SECTIONS'] ?? []),
        ]);
    }
}
