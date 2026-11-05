<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Support;

use Beeralex\Apiship\Dto\AddressDto;
use Beeralex\Apiship\Dto\CalculationContext;
use Beeralex\Apiship\Dto\ParcelDto;

/**
 * Правила валидации контекста расчёта доставки.
 * Не знает ни о SDK, ни о Bitrix — только бизнес-правила.
 */
class CalculationValidator
{
    protected const TIME_PATTERN = '/^([01]\d|2[0-3]):[0-5]\d$/';

    /**
     * @return string[] Список ошибок (пустой массив — запрос валиден)
     */
    public static function validate(CalculationContext $context): array
    {
        $errors = [];

        if ($context->places === []) {
            $errors[] = 'Не задано ни одного грузового места';
        }

        /** @var ParcelDto $place */
        foreach ($context->places as $i => $place) {
            foreach (['weight', 'width', 'height', 'length'] as $field) {
                if ((int)$place->{$field} <= 0) {
                    $errors[] = 'Место №' . ($i + 1) . ": не задан параметр {$field}";
                }
            }
        }

        array_push($errors, ...static::validateAddress($context->from, 'Адрес отправителя'));
        array_push($errors, ...static::validateAddress($context->to, 'Адрес получателя'));

        foreach (['pickupTypes', 'deliveryTypes'] as $field) {
            foreach ((array)$context->{$field} as $type) {
                if (!in_array((int)$type, [1, 2], true)) {
                    $errors[] = "Недопустимое значение в {$field}: {$type}";
                }
            }
        }

        foreach (['pickupTimeStart', 'pickupTimeEnd', 'deliveryTimeStart', 'deliveryTimeEnd'] as $field) {
            $value = (string)$context->{$field};
            if ($value !== '' && !preg_match(static::TIME_PATTERN, $value)) {
                $errors[] = "{$field}: ожидается формат ЧЧ:ММ";
            }
        }

        $date = (string)$context->pickupDate;
        if ($date !== '') {
            $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
            if ($parsed === false || $parsed->format('Y-m-d') !== $date) {
                $errors[] = 'pickupDate: ожидается формат ГГГГ-ММ-ДД';
            }
        }

        return $errors;
    }

    /**
     * @return string[]
     */
    protected static function validateAddress(AddressDto $address, string $label): array
    {
        $errors = [];

        if (trim((string)$address->countryCode) === '') {
            $errors[] = "{$label}: не указан код страны";
        }

        $hasLocation = false;
        foreach (['cityGuid', 'city', 'addressString'] as $key) {
            if (trim((string)$address->{$key}) !== '') {
                $hasLocation = true;
                break;
            }
        }
        if (!$hasLocation) {
            $errors[] = "{$label}: укажите город (city/cityGuid) или полный адрес";
        }

        return $errors;
    }
}
