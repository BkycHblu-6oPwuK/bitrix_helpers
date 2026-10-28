<?php

declare(strict_types=1);

namespace Beeralex\Api\Domain\Iblock;

use Beeralex\Api\Domain\File\FileDTO;
use Beeralex\Api\Domain\Location\LocationDTO;
use Beeralex\Core\Http\Resources\Resource;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string|null $type
 * @property string|null $userType
 * @property PropertyValueDTO[] $valuesDto
 * DTO свойства инфоблока: метаданные один раз, значения списком
 */
class PropertyItemDTO extends Resource
{
    /** Режим DISPLAY_PROPERTIES */
    public static function make(array $property): static
    {
        if (empty($property['VALUE']) && empty($property['DISPLAY_VALUE'])) {
            return new static([]);
        }

        $userType = $property['USER_TYPE'] ?? null;

        return new static([
            'id' => (int)($property['ID'] ?? 0),
            'code' => (string)$property['CODE'],
            'name' => (string)$property['NAME'],
            'type' => $property['PROPERTY_TYPE'] ?? null,
            'userType' => $userType,
            'valuesDto' => static::makeDisplayValues($property, $userType),
        ]);
    }

    /**
     * Режим декомпозиции ORM: принимает и одиночное значение, и список значений.
     * Вернёт null, если это не свойство.
     */
    public static function makeFromDecomposeData(array $property, string $code): ?static
    {
        $rows = isset($property['IBLOCK_PROPERTY_ID'])
            ? [$property]
            : array_values(array_filter(
                $property,
                static fn($row) => is_array($row) && isset($row['IBLOCK_PROPERTY_ID'])
            ));

        if (!$rows) {
            return null;
        }

        $meta = $rows[0]; // метаданные свойства одинаковы во всех строках

        return new static([
            'id' => (int)($meta['IBLOCK_PROPERTY_ID'] ?? 0),
            'code' => $code,
            'name' => $meta['NAME'] ?? '',
            'type' => $meta['PROPERTY_TYPE'] ?? null,
            'userType' => $meta['USER_TYPE'] ?? null,
            'valuesDto' => array_map([PropertyValueDTO::class, 'makeFromDecomposeData'], $rows),
        ]);
    }

    /**
     * @return PropertyValueDTO[]
     */
    protected static function makeDisplayValues(array $p, ?string $userType): array
    {
        $multiple = ($p['MULTIPLE'] ?? 'N') === 'Y';
        // DESCRIPTION и ~VALUE у множественных свойств приходят массивами по индексу
        $pick = static fn($v, int $i) => $multiple && is_array($v) ? ($v[$i] ?? null) : $v;
        $clean = $p['~VALUE'] ?? null;
        $desc = $p['DESCRIPTION'] ?? '';

        if (isset($p['FILE_VALUE']) && is_array($p['FILE_VALUE'])) {
            $files = isset($p['FILE_VALUE']['SRC']) ? [$p['FILE_VALUE']] : array_values($p['FILE_VALUE']);
            $result = [];
            foreach ($files as $i => $file) {
                if (isset($file['SRC'])) {
                    $result[] = PropertyValueDTO::make(FileDTO::make($file), $pick($clean, $i), (string)$pick($desc, $i));
                }
            }
            return $result;
        }

        if (!empty($p['VALUE_ENUM'])) {
            $list = is_array($p['VALUE_ENUM']) ? array_values($p['VALUE_ENUM']) : [$p['VALUE_ENUM']];
            $result = [];

            foreach ($list as $i => $text) {
                $result[] = PropertyValueDTO::make(
                    (string)$text,
                    $pick($clean, $i),
                    (string)$pick($desc, $i),
                    [
                        'ID'     => (int)$pick($p['VALUE_ENUM_ID'] ?? null, $i),
                        'XML_ID' => (string)($pick($p['VALUE_XML_ID'] ?? null, $i) ?? ''),
                        'SORT'   => (int)($pick($p['VALUE_SORT'] ?? null, $i) ?? 0),
                        'VALUE'  => $text,
                    ]
                );
            }

            return $result;
        }

        if (is_array($p['VALUE']) && isset($p['VALUE']['TEXT'])) {
            return [PropertyValueDTO::make(trim((string)$p['VALUE']['TEXT']), $clean, (string)$desc)];
        }

        if ($userType && str_contains($userType, 'map_') && !empty($p['VALUE'])) {
            [$lat, $lng] = array_pad(explode(',', (string)$p['VALUE']), 2, 0);
            $location = LocationDTO::make(['LATITUDE' => (float)$lat, 'LONGITUDE' => (float)$lng]);
            return [PropertyValueDTO::make($location, $clean, (string)$desc)];
        }

        // привязка к элементам: значение - ID элемента, item - данные элемента
        if (!empty($p['LINK_ELEMENT_VALUE'])) {
            $result = [];
            $i = 0;
            foreach ($p['LINK_ELEMENT_VALUE'] as $element) {
                $result[] = PropertyValueDTO::make($element['ID'] ?? null, $pick($clean, $i), (string)$pick($desc, $i), $element);
                $i++;
            }
            return $result;
        }

        // обычные значения (+ HL_DATA, если это справочник и значение одно)
        $raw = $p['DISPLAY_VALUE'] ?? null;
        $list = is_array($raw) ? array_values($raw) : [$raw];
        $item = $multiple ? null : ($p['HL_DATA'] ?? null);

        return array_map(
            fn($v, $i) => PropertyValueDTO::make($v, $pick($clean, $i), (string)$pick($desc, $i), $item),
            $list,
            array_keys($list)
        );
    }
}
