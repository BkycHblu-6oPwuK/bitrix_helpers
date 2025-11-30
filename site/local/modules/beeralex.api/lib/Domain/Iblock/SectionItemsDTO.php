<?php

namespace Beeralex\Api\Domain\Iblock;

use Beeralex\Api\Domain\Pagination\PaginationDTO;
use Beeralex\Core\Http\Resources\Resource;

/** 
 * @property CatalogItemDTO[] $items
 * @property PaginationDTO|null $pagination
 */
class SectionItemsDTO extends Resource
{
    public static function make(array $arResult): static
    {
        throw new \RuntimeException('Use makeFrom method');
    }

    /**
     * @param Resource[] $sectionItems
     */
    public static function makeFrom(array $sectionItems, ?PaginationDTO $pagination = null): static
    {
        return new static([
            'items' => $sectionItems,
            'pagination' => $pagination,
        ]);
    }
}
