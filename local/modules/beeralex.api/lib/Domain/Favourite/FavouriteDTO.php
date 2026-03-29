<?php
namespace Beeralex\Api\Domain\Favourite;

use Beeralex\Api\Domain\Iblock\Catalog\CatalogItemDTO;
use Beeralex\Api\Domain\Iblock\SectionItemsDTO;
use Beeralex\Api\Domain\Pagination\PaginationDTO;

/**
 * @property CatalogItemDTO[] $items
 * @property PaginationDTO $pagination
 */
class FavouriteDTO extends SectionItemsDTO 
{
    public static function make(array $arResult): static
    {
        return parent::makeFrom(array_map([CatalogItemDTO::class, 'make'], $arResult['ITEMS'] ?? []), PaginationDTO::make($arResult['PAGINATION'] ?? []));
    }
}