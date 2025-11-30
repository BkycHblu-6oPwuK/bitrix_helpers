<?php

namespace Beeralex\Api\Domain\Iblock\Catalog;

use Beeralex\Api\Domain\Pagination\PaginationDTO;
use Beeralex\Core\Http\Resources\Resource;

/** 
 * @property CatalogItemDTO[] $items
 * @property PaginationDTO|null $pagination
 */
class CatalogSectionDTO extends Resource
{
    public static function make(array $arResult): static
    {
        return new static([
            'items' => array_map(fn($sectionItem) => CatalogItemDTO::make($sectionItem), $arResult['ITEMS'] ?? []),
            'pagination' => $arResult['NAV_RESULT'] ? PaginationDTO::fromResult($arResult['NAV_RESULT']) : null,
        ]);
    }
}
