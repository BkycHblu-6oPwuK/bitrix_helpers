<?php

namespace Beeralex\Api\Domain\Iblock\Catalog;

use Beeralex\Api\Domain\Iblock\BreadcrumbsItemDTO;
use Beeralex\Core\Http\Resources\Resource;

/** 
 * @property CatalogItemDTO $element
 * @property BreadcrumbsItemDTO[] $path
 * DTO для элемента каталога
 */
class CatalogElementDTO extends Resource
{
    public static function make(array $data): static
    {
        throw new \RuntimeException('Use makeFrom method');
    }

    public static function makeFrom(array $elementData, array $arResult)
    {
        return new static([
            'element' => CatalogItemDTO::make($elementData),
            'path' => array_map([BreadcrumbsItemDTO::class, 'make'], $arResult['PATH'] ?? $arResult['SECTION']['PATH'] ?? []),
        ]);
    }
}
