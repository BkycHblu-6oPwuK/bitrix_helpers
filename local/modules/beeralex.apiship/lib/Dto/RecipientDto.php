<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Apiship\Support\Concerns\Arrayable;
use Beeralex\Apiship\Support\Concerns\NormalizesValues;

/**
 * Контакт с адресом: `recipient`, `sender` и `returnAddress` запроса создания заказа ApiShip.
 *
 * Если не заполнены countryCode/region/city/street, обязателен addressString (ApiShip разберёт его сам).
 * brandName относится только к sender, companyInn — к sender и recipient.
 */
class RecipientDto
{
    use Arrayable;
    use NormalizesValues;

    public function __construct(
        /** Контактный телефон. Обязательно. Пример: +7(925)111-11-11 */
        public readonly string $phone,
        /** ФИО контактного лица. Обязательно. Пример: Иванов Иван */
        public readonly string $contactName,
        /** Код страны ISO 3166-1 alpha-2. Обязательно. Пример: RU */
        public readonly string $countryCode,
        /** Область/республика/край. Пример: Москва */
        public readonly ?string $region,
        /** Город/населённый пункт. Пример: Москва */
        public readonly ?string $city,
        /** Улица. Пример: Нижняя Красносельская */
        public readonly ?string $street,
        /** Дом. Пример: 1a */
        public readonly ?string $house,
        /** Строение/корпус. Пример: 1 */
        public readonly ?string $block,
        /** Офис/квартира. Пример: 1 */
        public readonly ?string $office,
        /** Адрес одной строкой. Пример: 105066, Москва, Нижняя Красносельская улица, 14А */
        public readonly ?string $addressString,
        /** Контактный email */
        public readonly ?string $email,
        /** Почтовый индекс. Пример: 100100 */
        public readonly ?string $postIndex,
        /** Район. Пример: Кировский */
        public readonly ?string $area,
        /** ID города в ФИАС. Пример: 0c5b2444-70a0-4932-980c-b4dc0d3f02b5 */
        public readonly ?string $cityGuid,
        /** Широта. Пример: 55.756499 */
        public readonly ?float $lat,
        /** Долгота. Пример: 37.621864 */
        public readonly ?float $lng,
        /** Комментарий. Пример: Позвонить с проходной */
        public readonly ?string $comment,
        /** Название компании. Пример: ООО КНД */
        public readonly ?string $companyName,
        /** ИНН компании. Пример: 1234567890 */
        public readonly ?string $companyInn,
        /** Бренд продавца (только sender). Пример: ApiShip */
        public readonly ?string $brandName,
    ) {}

    public static function make(array $data): static
    {
        return new static(
            phone: (string)($data['phone'] ?? ''),
            contactName: (string)($data['contactName'] ?? ''),
            countryCode: (string)($data['countryCode'] ?? 'RU'),
            region: static::nullableString($data['region'] ?? null),
            city: static::nullableString($data['city'] ?? null),
            street: static::nullableString($data['street'] ?? null),
            house: static::nullableString($data['house'] ?? null),
            block: static::nullableString($data['block'] ?? null),
            office: static::nullableString($data['office'] ?? null),
            addressString: static::nullableString($data['addressString'] ?? null),
            email: static::nullableString($data['email'] ?? null),
            postIndex: static::nullableString($data['postIndex'] ?? null),
            area: static::nullableString($data['area'] ?? null),
            cityGuid: static::nullableString($data['cityGuid'] ?? null),
            lat: static::nullableFloat($data['lat'] ?? null),
            lng: static::nullableFloat($data['lng'] ?? null),
            comment: static::nullableString($data['comment'] ?? null),
            companyName: static::nullableString($data['companyName'] ?? null),
            companyInn: static::nullableString($data['companyInn'] ?? null),
            brandName: static::nullableString($data['brandName'] ?? null),
        );
    }
}
