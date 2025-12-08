<?php

namespace Beeralex\Api\Domain\Iblock\Catalog;

use Beeralex\Core\Http\Resources\Resource;

/** 
 * DTO для карты предложений
 * @property OffersTreePropsItemDTO[] $props Свойства, участвующие в предложениях
 * @property array $map Карта предложений
 */
class OffersTreeDTO extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'props' => array_map([OffersTreePropsItemDTO::class, 'make'], $data['PROPS'] ?? []),
            'map' => $data['MAP'] ?? [],
        ]);
    }
}
