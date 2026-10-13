<?php

namespace Beeralex\Api\Domain\Iblock;

use Beeralex\Api\Domain\Pagination\PaginationDTO;
use Beeralex\Core\Http\Resources\Resource;

/** 
 * @property ElementDTO[] $items
 * @property PaginationDTO|null $pagination
 * @property BreadcrumbsItemDTO[] $path
 * DTO для секции с элементами (каталог, статьи и т.д.)
 */
class SectionItemsDTO extends Resource
{
    public static function make(array $arResult): static
    {
        throw new \RuntimeException('Use makeFrom method');
    }

    /**
     * @param Resource[] $sectionItems
     * @param PaginationDTO|null $pagination
     * @param BreadcrumbsItemDTO[] $path
     */
    public static function makeFrom(array $sectionItems, ?PaginationDTO $pagination = null, array $path = []): static
    {
        return new static([
            'items' => array_values($sectionItems),
            'pagination' => $pagination,
            'path' => $path,
        ]);
    }
}
