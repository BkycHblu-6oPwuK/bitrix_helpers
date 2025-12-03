<?php

declare(strict_types=1);

namespace Beeralex\Api\Domain\Iblock;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property mixed $value
 * @property string|null $type
 * @property string|null $xmlId
 * @property string|null $link
 * @property string|null $pictureSrc
 * DTO для свойств инфоблока
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
            'id' => (int)($property['ID'] ?? 0),
            'code' => (string)$property['CODE'],
            'name' => (string)$property['NAME'],
            'value' => $value,
            'type' => $type,
            'xmlId' => $property['XML_ID'] ?? null,
            'link' => $property['LINK'] ?? null,
            'pictureSrc' => $property['PICTURE_SRC'] ?? null,
        ]);
    }

    /**
     * При декомпозиции свойство возвращается в виде массива, если это каталог - то данные хайлоада должны быть под ключом HL_DATA
     */
    public static function makeFromDecomposeData(array $property, string $code)
    {
        $hlData = $property['HL_DATA'] ?? [];
        return new static([
            'id' => (int)($property['ID'] ?? 0),
            'code' => $code,
            'name' => $property['NAME'] ?? '',
            'value' => $hlData['UF_NAME'] ?? $property['VALUE'] ?? null,
            'type' => $property['PROPERTY_TYPE'] ?? null,
            'xmlId' => $hlData['UF_XML_ID'] ?? $property['XML_ID'] ?? null,
            'link' => $hlData['UF_LINK'] ?? $property['LINK'] ?? null,
            'pictureSrc' => $hlData['PICTURE_SRC'] ?? $property['PICTURE_SRC'] ?? null,
        ]);
    }
}
