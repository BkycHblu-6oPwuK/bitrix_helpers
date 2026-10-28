<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Core\Http\Resources\Resource;

/**
 * Входные данные для расчёта стоимости доставки (POST /calculator).
 *
 * Обязательные для API: from, to, weight/width/height/length (заказ целиком), places.
 * Единицы: вес — граммы, габариты — сантиметры, деньги — рубли.
 *
 * @property array $from  Адрес отправителя
 * @property array $to    Адрес получателя
 * @property int $weight  Вес заказа, г
 * @property int $width   Ширина заказа, см
 * @property int $height  Высота заказа, см
 * @property int $length  Длина заказа, см
 * @property array<int,array{weight:int,width:int,height:int,length:int}> $places
 * @property float $assessedCost Оценочная стоимость, руб
 * @property float $codCost      Сумма наложенного платежа, руб
 * @property bool $includeFees   Суммировать сборы СД (страховка, комиссия за НП)
 * @property string $pickupDate  Дата приёма груза, Y-m-d ('' = сегодня)
 * @property array<int,int> $pickupTypes    1=забор от двери, 2=привоз на склад СД
 * @property array<int,int> $deliveryTypes  1=до двери, 2=до ПВЗ
 * @property array<int,string> $providerKeys
 * @property array<int,int> $tariffIds
 * @property int $timeout        Ожидание ответа провайдеров, мс (0 = не ограничивать)
 * @property bool $skipTariffRules
 * @property string $promoCode
 * @property string $customCode
 * @property int $pointInId      Склад СД (0 = не задан)
 * @property int $pointOutId     ПВЗ получателя (0 = не задан)
 * @property string $pickupTimeStart     ЧЧ:ММ
 * @property string $pickupTimeEnd       ЧЧ:ММ
 * @property string $deliveryTimeStart   ЧЧ:ММ
 * @property string $deliveryTimeEnd     ЧЧ:ММ
 * @property array<string,mixed> $extraParams Например ['dpd.providerConnectId' => 1234]
 */
final class CalculationRequestDto extends Resource
{
    private const TIME_PATTERN = '/^([01]\d|2[0-3]):[0-5]\d$/';

    public static function make(array $data): static
    {
        $places = static::normalizePlaces((array)($data['places'] ?? []));
        $total  = static::aggregatePlaces($places);

        return new static([
            'from'   => static::normalizeAddress((array)($data['from'] ?? [])),
            'to'     => static::normalizeAddress((array)($data['to'] ?? [])),

            // габариты и вес заказа целиком; если не переданы — считаем по местам
            'weight' => static::posInt($data['weight'] ?? 0) ?: $total['weight'],
            'width'  => static::posInt($data['width'] ?? 0) ?: $total['width'],
            'height' => static::posInt($data['height'] ?? 0) ?: $total['height'],
            'length' => static::posInt($data['length'] ?? 0) ?: $total['length'],
            'places' => $places,

            'assessedCost'    => round(max(0.0, (float)($data['assessedCost'] ?? 0)), 2),
            'codCost'         => round(max(0.0, (float)($data['codCost'] ?? 0)), 2),
            'includeFees'     => (bool)($data['includeFees'] ?? false),
            'pickupDate'      => trim((string)($data['pickupDate'] ?? '')),
            'pickupTypes'     => static::intList($data['pickupTypes'] ?? []),
            'deliveryTypes'   => static::intList($data['deliveryTypes'] ?? []),
            'providerKeys'    => static::stringList($data['providerKeys'] ?? []),
            'tariffIds'       => static::intList($data['tariffIds'] ?? []),
            'timeout'         => static::posInt($data['timeout'] ?? 0),
            'skipTariffRules' => (bool)($data['skipTariffRules'] ?? false),
            'promoCode'       => trim((string)($data['promoCode'] ?? '')),
            'customCode'      => trim((string)($data['customCode'] ?? '')),
            'pointInId'       => max(0, (int)($data['pointInId'] ?? 0)),
            'pointOutId'      => max(0, (int)($data['pointOutId'] ?? 0)),
            'pickupTimeStart'   => trim((string)($data['pickupTimeStart'] ?? '')),
            'pickupTimeEnd'     => trim((string)($data['pickupTimeEnd'] ?? '')),
            'deliveryTimeStart' => trim((string)($data['deliveryTimeStart'] ?? '')),
            'deliveryTimeEnd'   => trim((string)($data['deliveryTimeEnd'] ?? '')),
            'extraParams'     => array_filter(
                (array)($data['extraParams'] ?? []),
                static fn($v) => $v !== null && $v !== ''
            ),
        ]);
    }

    /**
     * Проверка обязательных полей по документации API.
     *
     * @return string[] Список ошибок (пусто — запрос валиден)
     */
    public function validate(): array
    {
        $errors = [];

        $totals = [
            'weight' => 'вес заказа',
            'width'  => 'ширина заказа',
            'height' => 'высота заказа',
            'length' => 'длина заказа',
        ];
        foreach ($totals as $field => $label) {
            if ((int)$this->{$field} <= 0) {
                $errors[] = "Не задано: {$label}";
            }
        }

        $places = (array)$this->places;
        if ($places === []) {
            $errors[] = 'Не задано ни одного грузового места';
        }
        foreach ($places as $i => $place) {
            foreach (['weight', 'width', 'height', 'length'] as $field) {
                if ((int)($place[$field] ?? 0) <= 0) {
                    $errors[] = 'Место №' . ($i + 1) . ": не задан параметр {$field}";
                }
            }
        }

        array_push($errors, ...static::validateAddress((array)$this->from, 'Адрес отправителя'));
        array_push($errors, ...static::validateAddress((array)$this->to, 'Адрес получателя'));

        foreach (['pickupTypes', 'deliveryTypes'] as $field) {
            foreach ((array)$this->{$field} as $type) {
                if (!in_array((int)$type, [1, 2], true)) {
                    $errors[] = "Недопустимое значение в {$field}: {$type}";
                }
            }
        }

        foreach (['pickupTimeStart', 'pickupTimeEnd', 'deliveryTimeStart', 'deliveryTimeEnd'] as $field) {
            $value = (string)$this->{$field};
            if ($value !== '' && !preg_match(static::TIME_PATTERN, $value)) {
                $errors[] = "{$field}: ожидается формат ЧЧ:ММ";
            }
        }

        $date = (string)$this->pickupDate;
        if ($date !== '') {
            $parsed = \DateTimeImmutable::createFromFormat('!Y-m-d', $date);
            if ($parsed === false || $parsed->format('Y-m-d') !== $date) {
                $errors[] = 'pickupDate: ожидается формат ГГГГ-ММ-ДД';
            }
        }

        return $errors;
    }

    /**
     * Тело запроса для API: обязательные поля всегда, необязательные — только если заданы.
     *
     * @return array<string,mixed>
     */
    public function toApiPayload(): array
    {
        $payload = [
            'from'   => (array)$this->from,
            'to'     => (array)$this->to,
            'weight' => (int)$this->weight,
            'width'  => (int)$this->width,
            'height' => (int)$this->height,
            'length' => (int)$this->length,
            'places' => array_values((array)$this->places),
        ];

        $optional = [
            'assessedCost'      => $this->assessedCost,
            'codCost'           => $this->codCost,
            'includeFees'       => $this->includeFees,
            'pickupDate'        => $this->pickupDate,
            'pickupTypes'       => $this->pickupTypes,
            'deliveryTypes'     => $this->deliveryTypes,
            'providerKeys'      => $this->providerKeys,
            'tariffIds'         => $this->tariffIds,
            'timeout'           => $this->timeout,
            'skipTariffRules'   => $this->skipTariffRules,
            'promoCode'         => $this->promoCode,
            'customCode'        => $this->customCode,
            'pointInId'         => $this->pointInId,
            'pointOutId'        => $this->pointOutId,
            'pickupTimeStart'   => $this->pickupTimeStart,
            'pickupTimeEnd'     => $this->pickupTimeEnd,
            'deliveryTimeStart' => $this->deliveryTimeStart,
            'deliveryTimeEnd'   => $this->deliveryTimeEnd,
            'extraParams'       => $this->extraParams,
        ];

        foreach ($optional as $key => $value) {
            if ($value === null || $value === '' || $value === [] || $value === 0 || $value === 0.0 || $value === false) {
                continue;
            }
            $payload[$key] = $value;
        }

        return $payload;
    }

    /**
     * @return string[]
     */
    private static function validateAddress(array $address, string $label): array
    {
        $errors = [];

        if (trim((string)($address['countryCode'] ?? '')) === '') {
            $errors[] = "{$label}: не указан код страны";
        }

        $hasLocation = false;
        foreach (['cityGuid', 'city', 'addressString'] as $key) {
            if (trim((string)($address[$key] ?? '')) !== '') {
                $hasLocation = true;
                break;
            }
        }
        if (!$hasLocation) {
            $errors[] = "{$label}: укажите город (city/cityGuid) или полный адрес";
        }

        return $errors;
    }

    /**
     * Убирает пустые значения, обрезает строки, страна по умолчанию — RU.
     *
     * @return array<string,mixed>
     */
    private static function normalizeAddress(array $address): array
    {
        $result = [];
        foreach ($address as $key => $value) {
            if (is_string($value)) {
                $value = trim($value);
            }
            if ($value === null || $value === '') {
                continue;
            }
            $result[$key] = $value;
        }

        $result['countryCode'] = strtoupper((string)($result['countryCode'] ?? 'RU'));

        return $result;
    }

    /**
     * @return array<int,array{weight:int,width:int,height:int,length:int}>
     */
    private static function normalizePlaces(array $places): array
    {
        $result = [];
        foreach ($places as $place) {
            if (!is_array($place)) {
                continue;
            }
            $result[] = [
                'weight' => static::posInt($place['weight'] ?? 0),
                'width'  => static::posInt($place['width'] ?? 0),
                'height' => static::posInt($place['height'] ?? 0),
                'length' => static::posInt($place['length'] ?? 0),
            ];
        }
        return $result;
    }

    /**
     * Итог по местам: вес — сумма, ширина/длина — максимум, высота — сумма (места друг на друге).
     *
     * @param array<int,array{weight:int,width:int,height:int,length:int}> $places
     * @return array{weight:int,width:int,height:int,length:int}
     */
    private static function aggregatePlaces(array $places): array
    {
        $total = ['weight' => 0, 'width' => 0, 'height' => 0, 'length' => 0];
        foreach ($places as $place) {
            $total['weight'] += $place['weight'];
            $total['height'] += $place['height'];
            $total['width']   = max($total['width'], $place['width']);
            $total['length']  = max($total['length'], $place['length']);
        }
        return $total;
    }

    private static function posInt(mixed $value): int
    {
        return max(0, (int)ceil((float)$value));
    }

    /**
     * @return array<int,int>
     */
    private static function intList(mixed $value): array
    {
        $list = array_filter(array_map('intval', (array)$value), static fn(int $v) => $v > 0);
        return array_values(array_unique($list));
    }

    /**
     * @return array<int,string>
     */
    private static function stringList(mixed $value): array
    {
        $list = array_filter(
            array_map(static fn($v) => trim((string)$v), (array)$value),
            static fn(string $v) => $v !== ''
        );
        return array_values(array_unique($list));
    }
}
