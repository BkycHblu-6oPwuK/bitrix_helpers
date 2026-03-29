<?php

declare(strict_types=1);

namespace Beeralex\Api\Domain\Iblock;

use Beeralex\Api\Domain\File\FileDTO;
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
 * @property int $fileSize
 * @property array|null $item
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
        $item = null;
        $type = $property['PROPERTY_TYPE'] ?? null;
        if (isset($property['FILE_VALUE'])) {
            if(is_array($property['FILE_VALUE']) && isset($property['FILE_VALUE']['SRC'])) {
                $value[] = FileDTO::make($property['FILE_VALUE']);
            } elseif (is_array($property['FILE_VALUE'])) {
                $files = [];
                foreach ($property['FILE_VALUE'] as $file) {
                    if (isset($file['SRC'])) {
                        $files[] = FileDTO::make($file);
                    }
                }
                $value = $files;
            }
        } elseif (is_array($property['VALUE']) && isset($property['VALUE']['TEXT'])) {
            $value = trim((string)$property['VALUE']['TEXT']);
        } elseif($property['VALUE_ENUM']) {
            $value = is_array($property['VALUE_ENUM']) ? $property['VALUE_ENUM'] : (string)$property['VALUE_ENUM'];
        } else {
            $value = !empty($property['LINK_ELEMENT_VALUE']) ? $property['LINK_ELEMENT_VALUE'][array_key_first($property['LINK_ELEMENT_VALUE'])]['ID'] : (string)$property['DISPLAY_VALUE'];
        }
        if(!empty($property['LINK_ELEMENT_VALUE'])) {
            $item = array_values(array_map([ElementDTO::class, 'make'], $property['LINK_ELEMENT_VALUE']));
        }
        return new static([
            'id' => (int)($property['ID'] ?? 0),
            'code' => (string)$property['CODE'],
            'name' => (string)$property['NAME'],
            'value' => $value,
            'type' => $type,
            'xmlId' => $property['VALUE_XML_ID'] ?? $property['XML_ID'] ?? null,
            'link' => $property['LINK'] ?? null,
            'pictureSrc' => $property['PICTURE_SRC'] ?? null,
            'item' => $item,
        ]);
    }

    /**
     * При декомпозиции свойство возвращается в виде массива, если это каталог - то данные хайлоада должны быть под ключом HL_DATA
     */
    public static function makeFromDecomposeData(array $property, string $code)
    {
        $hlData = $property['HL_DATA'] ?? [];
        $itemData = $property['LINK_ELEMENT_VALUE'] ? array_values(array_map([ElementDTO::class, 'fromDecomposeData'], $property['LINK_ELEMENT_VALUE'])) : $property['ITEM'] ?? [];
        
        return new static([
            'id' => (int)($property['ID'] ?? 0),
            'code' => $code,
            'name' => $property['NAME'] ?? '',
            'value' => $hlData['UF_NAME'] ?? $itemData['VALUE'] ?? $property['VALUE'] ?? null,
            'type' => $property['PROPERTY_TYPE'] ?? null,
            'xmlId' => $hlData['UF_XML_ID'] ?? $itemData['XML_ID'] ?? null,
            'link' => $hlData['UF_LINK'] ?? $property['LINK'] ?? null,
            'pictureSrc' => $hlData['PICTURE_SRC'] ?? $property['PICTURE_SRC'] ?? null,
            'item' => $hlData ? $hlData : ($itemData ? $itemData : null),
        ]);
    }
}
