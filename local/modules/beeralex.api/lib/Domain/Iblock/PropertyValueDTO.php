<?php

declare(strict_types=1);

namespace Beeralex\Api\Domain\Iblock;

use Beeralex\Api\Domain\File\FileDTO;
use Beeralex\Core\Http\Resources\Resource;

/**
 * @property string|array|int|float|object|null $value
 * @property mixed $cleanValue
 * @property string $description
 * @property mixed $item
 * Одно значение свойства (общие данные свойства лежат в PropertyItemDTO)
 */
class PropertyValueDTO extends Resource
{
    public static function make(mixed $value, mixed $cleanValue = null, string $description = '', mixed $item = null): static
    {
        return new static([
            'value' => $value,
            'cleanValue' => $cleanValue,
            'description' => $description,
            'item' => $item,
        ]);
    }

    public static function makeFromDecomposeData(array $row): static
    {
        $description = (string)($row['DESCRIPTION'] ?? '');

        $file = $row['FILE_VALUE'] ?? null;
        if (is_array($file) && isset($file['SRC'])) {
            return static::make(FileDTO::make($file), $row['VALUE'] ?? null, $description, null);
        }

        $hlData = $row['HL_DATA'] ?? [];
        $itemData = $row['LINK_ELEMENT_VALUE'] ?? $row['ITEM'] ?? [];
        $value = $hlData['UF_NAME'] ?? $itemData['VALUE'] ?? $row['VALUE'] ?? null;

        return static::make($value, $value, $description, $hlData ?: ($itemData ?: null));
    }
}
