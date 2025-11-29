<?php
declare(strict_types=1);

namespace Beeralex\Api\Domain\Iblock\Content;

use Beeralex\Api\Domain\Iblock\Catalog\CatalogItemDTO;
use Beeralex\Core\Http\Resources\Resource;

/**
 * @property string $title
 * @property string $linkToAll
 * @property CatalogItemDTO[] $items
 */
class ProductSliderDTO extends Resource
{
    /**
     * @param array{TITLE: string, LINK_TO_ALL: string, ITEMS: array} $data
     */
    public static function make(array $data): static
    {
        return new static([
            'title' => $data['TITLE'] ?? '',
            'linkToAll' => $data['LINK_TO_ALL'] ?? '',
            'items' => array_map([CatalogItemDTO::class, 'make'],
                $data['ITEMS'] ?? []
            ),
        ]);
    }
}
