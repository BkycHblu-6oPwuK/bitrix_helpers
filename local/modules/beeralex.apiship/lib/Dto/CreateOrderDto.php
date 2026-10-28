<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Apiship\Dto\Concerns\NormalizesValues;
use Beeralex\Core\Http\Resources\Resource;

/**
 * Входные данные для создания заказа ApiShip (POST /orders).
 *
 * Собирается ApiShipOrderRequestBuilder из заказа Bitrix. Поля блока `order` — плоские,
 * остальные блоки вложены (as array): cost — CostDto, sender/recipient/returnAddress — RecipientDto,
 * places — PlaceDto[].
 *
 * @property int $bitrixOrderId ID заказа Bitrix (служебное поле, в ApiShip не уходит)
 *
 * Блок order:
 * @property string $clientNumber Номер заказа в системе клиента. Обязательно. Пример: 123-456
 * @property string $providerKey Код службы доставки. Обязательно. Пример: cdek
 * @property int $tariffId ID тарифа СД. Обязательно. Пример: 1
 * @property int $pickupType Тип забора: 1 — от двери клиента, 2 — клиент привозит на склад СД. Обязательно
 * @property int $deliveryType Тип доставки: 1 — до двери, 2 — до ПВЗ. Обязательно
 * @property int $weight Вес всего заказа, г. Обязательно. Пример: 100
 * @property int|null $providerConnectId ID подключения к СД (если договоров с одной СД несколько). Пример: 12
 * @property string|null $description Комментарий к заказу. Пример: Важный заказ
 * @property string|null $barcode Штрихкод заказа (при передаче places — указывается в place.barcode)
 * @property int|null $height Высота заказа, см
 * @property int|null $length Длина заказа, см
 * @property int|null $width Ширина заказа, см
 * @property int|null $pointInId ID пункта приёма из /lists/points; обязателен при pickupType = 2
 * @property int|null $pointOutId ID пункта выдачи из /lists/points; обязателен при deliveryType = 2
 * @property string|null $pickupDate Планируемая дата передачи заказа в СД, Y-m-d. Пример: 2020-01-31
 * @property string|null $pickupTimeStart Начало времени передачи в СД, H:i. Пример: 10:00
 * @property string|null $pickupTimeEnd Конец времени передачи в СД, H:i. Пример: 18:00
 * @property string|null $deliveryDate Желаемая дата доставки, Y-m-d
 * @property string|null $deliveryTimeStart Начало времени доставки, H:i
 * @property string|null $deliveryTimeEnd Конец времени доставки, H:i
 *
 * Вложенные блоки:
 * @property array<string,mixed> $cost CostDto as array. Обязательно
 * @property array<string,mixed> $sender RecipientDto as array. Обязательно
 * @property array<string,mixed> $recipient RecipientDto as array. Обязательно
 * @property array<string,mixed> $returnAddress RecipientDto as array; пусто — не передаётся
 * @property array<int,array<string,mixed>> $places PlaceDto[] as array. Обязательно
 * @property array<int,array{key:string,value:string}> $extraParams Доп. услуги СД. Пример: [['key' => 'cdek.InspectionAttachments', 'value' => '1']]
 */
final class CreateOrderDto extends Resource
{
    use NormalizesValues;

    public static function make(array $data): static
    {
        return new static([
            'bitrixOrderId'     => (int)($data['bitrixOrderId'] ?? 0),
            'clientNumber'      => (string)($data['clientNumber'] ?? ''),
            'providerKey'       => (string)($data['providerKey'] ?? ''),
            'tariffId'          => (int)($data['tariffId'] ?? 0),
            'pickupType'        => (int)($data['pickupType'] ?? 1),
            'deliveryType'      => (int)($data['deliveryType'] ?? 1),
            'weight'            => (int)($data['weight'] ?? 0),
            'providerConnectId' => static::nullableInt($data['providerConnectId'] ?? null),
            'description'       => static::nullableString($data['description'] ?? null),
            'barcode'           => static::nullableString($data['barcode'] ?? null),
            'height'            => static::nullableInt($data['height'] ?? null),
            'length'            => static::nullableInt($data['length'] ?? null),
            'width'             => static::nullableInt($data['width'] ?? null),
            'pointInId'         => static::nullableInt($data['pointInId'] ?? null),
            'pointOutId'        => static::nullableInt($data['pointOutId'] ?? null),
            'pickupDate'        => static::nullableString($data['pickupDate'] ?? null),
            'pickupTimeStart'   => static::nullableString($data['pickupTimeStart'] ?? null),
            'pickupTimeEnd'     => static::nullableString($data['pickupTimeEnd'] ?? null),
            'deliveryDate'      => static::nullableString($data['deliveryDate'] ?? null),
            'deliveryTimeStart' => static::nullableString($data['deliveryTimeStart'] ?? null),
            'deliveryTimeEnd'   => static::nullableString($data['deliveryTimeEnd'] ?? null),
            'cost'              => CostDto::make((array)($data['cost'] ?? []))->toArray(),
            'sender'            => RecipientDto::make((array)($data['sender'] ?? []))->toArray(),
            'recipient'         => RecipientDto::make((array)($data['recipient'] ?? []))->toArray(),
            'returnAddress'     => empty($data['returnAddress'])
                ? []
                : RecipientDto::make((array)$data['returnAddress'])->toArray(),
            'places'            => array_map(
                static fn($place) => PlaceDto::make((array)$place)->toArray(),
                array_values((array)($data['places'] ?? []))
            ),
            'extraParams'       => array_values((array)($data['extraParams'] ?? [])),
        ]);
    }
}
