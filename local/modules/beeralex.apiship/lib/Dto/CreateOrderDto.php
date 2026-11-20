<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Apiship\Support\Concerns\Arrayable;
use Beeralex\Apiship\Support\Concerns\NormalizesValues;

/**
 * Входные данные для создания заказа ApiShip (POST /orders).
 *
 * Собирается OrderService::collectDto() из заказа Bitrix, SDK-запрос строит OrderRequestBuilder.
 * Поля блока `order` — плоские, остальные блоки вложены как DTO: cost — CostDto,
 * sender/recipient/returnAddress — RecipientDto, places — PlaceDto[] (внутри ItemDto[]).
 *
 * make() принимает как готовые DTO, так и массивы и сам приводит типы.
 */
class CreateOrderDto
{
    use Arrayable;
    use NormalizesValues;

    public function __construct(
        /** ID заказа Bitrix (служебное поле, в ApiShip не уходит) */
        public readonly int $bitrixOrderId,
        /** Номер заказа в системе клиента. Обязательно. Пример: 123-456 */
        public readonly string $clientNumber,
        /** Код службы доставки. Обязательно. Пример: cdek */
        public readonly string $providerKey,
        /** ID тарифа СД. Обязательно. Пример: 1 */
        public readonly int $tariffId,
        /** Тип забора: 1 — от двери клиента, 2 — клиент привозит на склад СД. Обязательно */
        public readonly int $pickupType,
        /** Тип доставки: 1 — до двери, 2 — до ПВЗ. Обязательно */
        public readonly int $deliveryType,
        /** Вес всего заказа, г. Обязательно. Пример: 100 */
        public readonly int $weight,
        /** ID подключения (в OpenAPI — строка) к СД (если договоров с одной СД несколько). Пример: 12 */
        public readonly ?string $providerConnectId,
        /** Комментарий к заказу. Пример: Важный заказ */
        public readonly ?string $description,
        /** Штрихкод заказа (при передаче places — указывается в place.barcode) */
        public readonly ?string $barcode,
        /** Высота заказа, см */
        public readonly ?int $height,
        /** Длина заказа, см */
        public readonly ?int $length,
        /** Ширина заказа, см */
        public readonly ?int $width,
        /** ID пункта приёма из /lists/points; обязателен при pickupType = 2 */
        public readonly ?int $pointInId,
        /** ID пункта выдачи из /lists/points; обязателен при deliveryType = 2 */
        public readonly ?int $pointOutId,
        /** Планируемая дата передачи заказа в СД, Y-m-d. Пример: 2020-01-31 */
        public readonly ?string $pickupDate,
        /** Начало времени передачи в СД, H:i. Пример: 10:00 */
        public readonly ?string $pickupTimeStart,
        /** Конец времени передачи в СД, H:i. Пример: 18:00 */
        public readonly ?string $pickupTimeEnd,
        /** Желаемая дата доставки, Y-m-d */
        public readonly ?string $deliveryDate,
        /** Начало времени доставки, H:i */
        public readonly ?string $deliveryTimeStart,
        /** Конец времени доставки, H:i */
        public readonly ?string $deliveryTimeEnd,
        /** Обязательно */
        public readonly CostDto $cost,
        /** Отправитель. Обязательно */
        public readonly RecipientDto $sender,
        /** Получатель. Обязательно */
        public readonly RecipientDto $recipient,
        /** null — не передаётся */
        public readonly ?RecipientDto $returnAddress,
        /** @var PlaceDto[] Грузовые места. Обязательно */
        public readonly array $places,
        /** Доп. услуги СД. Пример: [['key' => 'cdek.InspectionAttachments', 'value' => '1']] */
        public readonly array $extraParams,
    ) {}

    public static function make(array $data): static
    {
        return new static(
            bitrixOrderId: (int)($data['bitrixOrderId'] ?? 0),
            clientNumber: (string)($data['clientNumber'] ?? ''),
            providerKey: (string)($data['providerKey'] ?? ''),
            tariffId: (int)($data['tariffId'] ?? 0),
            pickupType: (int)($data['pickupType'] ?? 1),
            deliveryType: (int)($data['deliveryType'] ?? 1),
            weight: (int)($data['weight'] ?? 0),
            providerConnectId: static::nullableString($data['providerConnectId'] ?? null),
            description: static::nullableString($data['description'] ?? null),
            barcode: static::nullableString($data['barcode'] ?? null),
            height: static::nullableInt($data['height'] ?? null),
            length: static::nullableInt($data['length'] ?? null),
            width: static::nullableInt($data['width'] ?? null),
            pointInId: static::nullableInt($data['pointInId'] ?? null),
            pointOutId: static::nullableInt($data['pointOutId'] ?? null),
            pickupDate: static::nullableString($data['pickupDate'] ?? null),
            pickupTimeStart: static::nullableString($data['pickupTimeStart'] ?? null),
            pickupTimeEnd: static::nullableString($data['pickupTimeEnd'] ?? null),
            deliveryDate: static::nullableString($data['deliveryDate'] ?? null),
            deliveryTimeStart: static::nullableString($data['deliveryTimeStart'] ?? null),
            deliveryTimeEnd: static::nullableString($data['deliveryTimeEnd'] ?? null),
            cost: ($data['cost'] ?? null) instanceof CostDto ? $data['cost'] : CostDto::make((array)($data['cost'] ?? [])),
            sender: static::contact($data['sender'] ?? []),
            recipient: static::contact($data['recipient'] ?? []),
            returnAddress: empty($data['returnAddress']) ? null : static::contact($data['returnAddress']),
            places: array_map(
                static fn($place) => $place instanceof PlaceDto ? $place : PlaceDto::make((array)$place),
                array_values((array)($data['places'] ?? []))
            ),
            extraParams: array_values((array)($data['extraParams'] ?? [])),
        );
    }

    /** @param array<string,mixed>|RecipientDto $contact */
    protected static function contact(mixed $contact): RecipientDto
    {
        return $contact instanceof RecipientDto ? $contact : RecipientDto::make((array)$contact);
    }
}
