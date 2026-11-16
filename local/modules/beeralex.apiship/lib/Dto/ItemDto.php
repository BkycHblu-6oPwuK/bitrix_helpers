<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Apiship\Support\Concerns\Arrayable;
use Beeralex\Apiship\Support\Concerns\NormalizesValues;

/**
 * Товарная позиция (`places[].items[]` запроса создания заказа ApiShip).
 */
class ItemDto
{
    use Arrayable;
    use NormalizesValues;

    public function __construct(
        /** Наименование товара. Обязательно. Пример: Кирпич */
        public readonly string $description,
        /** Количество. Обязательно. Пример: 2 */
        public readonly int $quantity,
        /** Вес единицы, г. Обязательно. Пример: 100 */
        public readonly int $weight,
        /** Артикул. Пример: 123-456 */
        public readonly ?string $articul,
        /** Штрихкод предмета. Пример: 123456 */
        public readonly ?string $barcode,
        /** Оценочная стоимость единицы, руб. Сумма assessedCost × quantity должна равняться cost.assessedCost */
        public readonly ?float $assessedCost,
        /** Наложенная стоимость единицы, руб. Пример: 100.20 */
        public readonly ?float $cost,
        /** Ставка НДС: -1 без НДС, 0, 5, 7, 10, 20, 22 */
        public readonly ?int $costVat,
        /** Высота единицы, см */
        public readonly ?int $height,
        /** Длина единицы, см */
        public readonly ?int $length,
        /** Ширина единицы, см */
        public readonly ?int $width,
        /** Код маркировки. Пример: 010468006347633 */
        public readonly ?string $markCode,
        /** Поставщик/продавец: наименование */
        public readonly ?string $companyName,
        /** Поставщик/продавец: ИНН. Пример: 1234567890 */
        public readonly ?string $companyInn,
        /** Поставщик/продавец: телефон. Пример: 79887776655 */
        public readonly ?string $companyPhone,
        /** Код ТН ВЭД. Пример: 6810190009 */
        public readonly ?string $tnved,
        /** Ссылка на страницу товара в магазине */
        public readonly ?string $url,
        /** Внешние URL фото товара (до 2048 символов каждый); null — не менять, [] — очистить */
        public readonly ?array $photoLinks,
    ) {}

    public static function make(array $data): static
    {
        return new static(
            description: (string)($data['description'] ?? ''),
            quantity: (int)($data['quantity'] ?? 1),
            weight: (int)($data['weight'] ?? 0),
            articul: static::nullableString($data['articul'] ?? null),
            barcode: static::nullableString($data['barcode'] ?? null),
            assessedCost: static::nullableFloat($data['assessedCost'] ?? null),
            cost: static::nullableFloat($data['cost'] ?? null),
            costVat: static::nullableInt($data['costVat'] ?? null),
            height: static::nullableInt($data['height'] ?? null),
            length: static::nullableInt($data['length'] ?? null),
            width: static::nullableInt($data['width'] ?? null),
            markCode: static::nullableString($data['markCode'] ?? null),
            companyName: static::nullableString($data['companyName'] ?? null),
            companyInn: static::nullableString($data['companyInn'] ?? null),
            companyPhone: static::nullableString($data['companyPhone'] ?? null),
            tnved: static::nullableString($data['tnved'] ?? null),
            url: static::nullableString($data['url'] ?? null),
            photoLinks: isset($data['photoLinks']) ? array_values(array_map('strval', (array)$data['photoLinks'])) : null,
        );
    }
}
