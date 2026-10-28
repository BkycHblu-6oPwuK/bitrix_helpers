<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Apiship\Dto\Concerns\NormalizesValues;
use Beeralex\Core\Http\Resources\Resource;

/**
 * Товарная позиция (`places[].items[]` запроса создания заказа ApiShip).
 *
 * @property string $description Наименование товара. Обязательно. Пример: Кирпич
 * @property int $quantity Количество. Обязательно. Пример: 2
 * @property int $weight Вес единицы, г. Обязательно. Пример: 100
 * @property string|null $articul Артикул. Пример: 123-456
 * @property string|null $barcode Штрихкод предмета. Пример: 123456
 * @property float|null $assessedCost Оценочная стоимость единицы, руб. Сумма assessedCost × quantity должна равняться cost.assessedCost
 * @property float|null $cost Наложенная стоимость единицы, руб. Пример: 100.20
 * @property int|null $costVat Ставка НДС: -1 без НДС, 0, 5, 7, 10, 20, 22
 * @property int|null $height Высота единицы, см
 * @property int|null $length Длина единицы, см
 * @property int|null $width Ширина единицы, см
 * @property string|null $markCode Код маркировки. Пример: 010468006347633
 * @property string|null $companyName Поставщик/продавец: наименование
 * @property string|null $companyInn Поставщик/продавец: ИНН. Пример: 1234567890
 * @property string|null $companyPhone Поставщик/продавец: телефон. Пример: 79887776655
 * @property string|null $tnved Код ТН ВЭД. Пример: 6810190009
 * @property string|null $url Ссылка на страницу товара в магазине
 * @property array<int,string>|null $photoLinks Внешние URL фото товара (до 2048 символов каждый); null — не менять, [] — очистить
 */
final class ItemDto extends Resource
{
    use NormalizesValues;

    public static function make(array $data): static
    {
        return new static([
            'description'  => (string)($data['description'] ?? ''),
            'quantity'     => (int)($data['quantity'] ?? 1),
            'weight'       => (int)($data['weight'] ?? 0),
            'articul'      => static::nullableString($data['articul'] ?? null),
            'barcode'      => static::nullableString($data['barcode'] ?? null),
            'assessedCost' => static::nullableFloat($data['assessedCost'] ?? null),
            'cost'         => static::nullableFloat($data['cost'] ?? null),
            'costVat'      => static::nullableInt($data['costVat'] ?? null),
            'height'       => static::nullableInt($data['height'] ?? null),
            'length'       => static::nullableInt($data['length'] ?? null),
            'width'        => static::nullableInt($data['width'] ?? null),
            'markCode'     => static::nullableString($data['markCode'] ?? null),
            'companyName'  => static::nullableString($data['companyName'] ?? null),
            'companyInn'   => static::nullableString($data['companyInn'] ?? null),
            'companyPhone' => static::nullableString($data['companyPhone'] ?? null),
            'tnved'        => static::nullableString($data['tnved'] ?? null),
            'url'          => static::nullableString($data['url'] ?? null),
            'photoLinks'   => isset($data['photoLinks']) ? array_values(array_map('strval', (array)$data['photoLinks'])) : null,
        ]);
    }
}
