<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Apiship\Dto\Concerns\NormalizesValues;
use Beeralex\Core\Http\Resources\Resource;

/**
 * Контакт с адресом: `recipient`, `sender` и `returnAddress` запроса создания заказа ApiShip.
 *
 * Если не заполнены countryCode/region/city/street, обязателен addressString (ApiShip разберёт его сам).
 * brandName относится только к sender, companyInn — к sender и recipient.
 *
 * @property string $phone Контактный телефон. Обязательно. Пример: +7(925)111-11-11
 * @property string $contactName ФИО контактного лица. Обязательно. Пример: Иванов Иван
 * @property string $countryCode Код страны ISO 3166-1 alpha-2. Обязательно. Пример: RU
 * @property string|null $region Область/республика/край. Пример: Москва
 * @property string|null $city Город/населённый пункт. Пример: Москва
 * @property string|null $street Улица. Пример: Нижняя Красносельская
 * @property string|null $house Дом. Пример: 1a
 * @property string|null $block Строение/корпус. Пример: 1
 * @property string|null $office Офис/квартира. Пример: 1
 * @property string|null $addressString Адрес одной строкой. Пример: 105066, Москва, Нижняя Красносельская улица, 14А
 * @property string|null $email Контактный email
 * @property string|null $postIndex Почтовый индекс. Пример: 100100
 * @property string|null $area Район. Пример: Кировский
 * @property string|null $cityGuid ID города в ФИАС. Пример: 0c5b2444-70a0-4932-980c-b4dc0d3f02b5
 * @property float|null $lat Широта. Пример: 55.756499
 * @property float|null $lng Долгота. Пример: 37.621864
 * @property string|null $comment Комментарий. Пример: Позвонить с проходной
 * @property string|null $companyName Название компании. Пример: ООО КНД
 * @property string|null $companyInn ИНН компании. Пример: 1234567890
 * @property string|null $brandName Бренд продавца (только sender). Пример: ApiShip
 */
final class RecipientDto extends Resource
{
    use NormalizesValues;

    public static function make(array $data): static
    {
        return new static([
            'phone'         => (string)($data['phone'] ?? ''),
            'contactName'   => (string)($data['contactName'] ?? ''),
            'countryCode'   => (string)($data['countryCode'] ?? 'RU'),
            'region'        => self::nullableString($data['region'] ?? null),
            'city'          => self::nullableString($data['city'] ?? null),
            'street'        => self::nullableString($data['street'] ?? null),
            'house'         => self::nullableString($data['house'] ?? null),
            'block'         => self::nullableString($data['block'] ?? null),
            'office'        => self::nullableString($data['office'] ?? null),
            'addressString' => self::nullableString($data['addressString'] ?? null),
            'email'         => self::nullableString($data['email'] ?? null),
            'postIndex'     => self::nullableString($data['postIndex'] ?? null),
            'area'          => self::nullableString($data['area'] ?? null),
            'cityGuid'      => self::nullableString($data['cityGuid'] ?? null),
            'lat'           => self::nullableFloat($data['lat'] ?? null),
            'lng'           => self::nullableFloat($data['lng'] ?? null),
            'comment'       => self::nullableString($data['comment'] ?? null),
            'companyName'   => self::nullableString($data['companyName'] ?? null),
            'companyInn'    => self::nullableString($data['companyInn'] ?? null),
            'brandName'     => self::nullableString($data['brandName'] ?? null),
        ]);
    }
}
