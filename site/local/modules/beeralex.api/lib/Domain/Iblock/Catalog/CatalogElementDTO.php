<?php

namespace Beeralex\Api\Domain\Iblock\Catalog;

use Beeralex\Api\Domain\Pagination\PaginationDTO;
use Beeralex\Core\Http\Resources\Resource;

/** 
 * @property CatalogItemDTO[] $items
 * @property PaginationDTO|null $pagination
 */
class CatalogElementDTO extends Resource
{
    public static function make(array $data): static
    {
        throw new \RuntimeException('Use makeFrom method');
    }

    public static function makeFrom(array $elementData, array $arResult)
    {
        $item = [];
        return new static([
            'element' => $item,
        ]);
    }
}
