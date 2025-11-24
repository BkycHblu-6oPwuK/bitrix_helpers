<?php
declare(strict_types=1);

namespace Beeralex\Api\Domain\Iblock;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property string $code
 * @property string $name
 * @property mixed $value
 * @property string|null $type
 */
class PropertyItemDTO extends Resource
{
    public static function make(array $property): static
    {
        if (empty($property['VALUE']) && empty($property['DISPLAY_VALUE'])) {
            return new static([]);
        }

        $value = null;
        $type = $property['PROPERTY_TYPE'] ?? null;

        if (isset($property['FILE_VALUE']['SRC'])) {
            $value = $property['FILE_VALUE']['SRC'];
        } elseif (is_array($property['VALUE']) && isset($property['VALUE']['TEXT'])) {
            $value = trim((string)$property['VALUE']['TEXT']);
        } else {
            $value = (string)$property['DISPLAY_VALUE'];
        }

        return new static([
            'code' => (string)$property['CODE'],
            'name' => (string)$property['NAME'],
            'value' => $value,
            'type' => $type,
        ]);
    }
}