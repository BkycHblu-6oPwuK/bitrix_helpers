<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Apiship\Dto\Concerns\NormalizesValues;
use Beeralex\Core\Http\Resources\Resource;

/**
 * Грузовое место (`places[]` запроса создания заказа ApiShip; также используется в расчёте).
 *
 * @property int $weight Вес места, г. Обязательно. Пример: 100
 * @property array<int,array<string,mixed>> $items Содержимое места (ItemDto as array). Обязательно
 * @property string|null $placeNumber Номер места в системе клиента. Пример: 1
 * @property string|null $barcode Штрихкод места. Пример: 011-819
 * @property int|null $height Высота, см
 * @property int|null $width Ширина, см
 * @property int|null $length Длина, см
 */
final class PlaceDto extends Resource
{
    use NormalizesValues;

    public static function make(array $data): static
    {
        return new static([
            'weight'      => (int)($data['weight'] ?? 0),
            'items'       => array_map(
                static fn($item) => ItemDto::make((array)$item)->toArray(),
                array_values((array)($data['items'] ?? []))
            ),
            'placeNumber' => static::nullableString($data['placeNumber'] ?? null),
            'barcode'     => static::nullableString($data['barcode'] ?? null),
            'height'      => static::nullableInt($data['height'] ?? null),
            'width'       => static::nullableInt($data['width'] ?? null),
            'length'      => static::nullableInt($data['length'] ?? null),
        ]);
    }
}
