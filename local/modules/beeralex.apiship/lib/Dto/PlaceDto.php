<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Apiship\Support\Concerns\Arrayable;
use Beeralex\Apiship\Support\Concerns\NormalizesValues;

/**
 * Грузовое место (`places[]` запроса создания заказа ApiShip; также используется в расчёте).
 */
class PlaceDto
{
    use Arrayable;
    use NormalizesValues;

    public function __construct(
        /** Вес места, г. Обязательно. Пример: 100 */
        public readonly int $weight,
        /** @var ItemDto[] Содержимое места. Обязательно */
        public readonly array $items,
        /** Номер места в системе клиента. Пример: 1 */
        public readonly ?string $placeNumber,
        /** Штрихкод места. Пример: 011-819 */
        public readonly ?string $barcode,
        /** Высота, см */
        public readonly ?int $height,
        /** Ширина, см */
        public readonly ?int $width,
        /** Длина, см */
        public readonly ?int $length,
    ) {}

    public static function make(array $data): static
    {
        return new static(
            weight: (int)($data['weight'] ?? 0),
            items: array_map(
                static fn($item) => $item instanceof ItemDto ? $item : ItemDto::make((array)$item),
                array_values((array)($data['items'] ?? []))
            ),
            placeNumber: static::nullableString($data['placeNumber'] ?? null),
            barcode: static::nullableString($data['barcode'] ?? null),
            height: static::nullableInt($data['height'] ?? null),
            width: static::nullableInt($data['width'] ?? null),
            length: static::nullableInt($data['length'] ?? null),
        );
    }
}
