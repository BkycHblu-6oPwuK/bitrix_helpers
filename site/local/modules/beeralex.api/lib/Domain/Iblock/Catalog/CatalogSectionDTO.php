<?php

namespace Beeralex\Api\Domain\Iblock\Catalog;

use Beeralex\Api\Domain\Iblock\SectionItemsDTO;
use Beeralex\Api\Domain\Pagination\PaginationDTO;

/** 
 * @property CatalogItemDTO[] $items
 * @property PaginationDTO|null $pagination
 * DTO для catalog.section
 */
class CatalogSectionDTO extends SectionItemsDTO
{
    public static function make(array $arResult): static
    {
        return parent::makeFrom(
            array_map([CatalogItemDTO::class, 'make'], $arResult['ITEMS'] ?? []),
            $arResult['NAV_RESULT'] ? PaginationDTO::fromResult($arResult['NAV_RESULT']) : null,
        );
    }
}
