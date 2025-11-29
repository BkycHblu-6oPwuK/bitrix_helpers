<?php

namespace Beeralex\Api\Domain\Iblock\Catalog;

use Beeralex\Core\Http\Resources\Resource;

/** 
 * @property string $controlId
 * @property string $htmlValue
 * @property string $value
 * @property bool $checked
 * @property bool $disabled
 */
class CatalogFilterValueItemDTO extends Resource
{
    public static function make(array $valueItem): static
    {
        return new static([
            'controlId' => $valueItem['CONTROL_ID'] ?? '',
            'htmlValue' => $valueItem['HTML_VALUE'] ?? '',
            'value' => $valueItem['VALUE'] ?? '',
            'checked' => $valueItem['CHECKED'] ?? false,
            'disabled' => $valueItem['DISABLED'] ?? false,
        ]);
    }
}
