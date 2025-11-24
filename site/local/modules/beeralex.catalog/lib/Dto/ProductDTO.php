<?php

namespace Beeralex\Catalog\Dto;

use Beeralex\Core\Http\Resources\Resource;

/**
 * DTO для товара с динамическими свойствами.
 * 
 * @property int $id
 * @property string $name
 * @property string $code
 * @property bool $active
 * @property bool $available
 * @property int $sectionId
 * @property array $prices
 * @property array $offers
 * @property ?string $url
 * @property ?string $imageSrc
 */
class ProductDTO extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'id' => (int)$data['ID'],
            'name' => (string)$data['NAME'],
            'code' => (string)$data['CODE'],
            'active' => (bool)$data['ACTIVE'],
            'available' => (bool)$data['AVAILABLE'],
            'sectionId' => (int)$data['SECTION_ID'],
            'prices' => $data['PRICES'] ?? [],
            'offers' => $data['OFFERS'] ?? [],
            'url' => isset($data['URL']) ? (string)$data['URL'] : null,
            'imageSrc' => isset($data['IMAGE_SRC']) ? (string)$data['IMAGE_SRC'] : null,
        ]);
    }
}
