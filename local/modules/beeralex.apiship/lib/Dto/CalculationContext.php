<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Apiship\Support\Concerns\NormalizesValues;
use Beeralex\Core\Http\Resources\Resource;

/**
 * Контекст запроса расчёта стоимости доставки — только данные и нормализация типов.
 * Без валидации, без формирования SDK-запроса.
 *
 * Единицы: вес — граммы, габариты — сантиметры, деньги — рубли.
 *
 * @property AddressDto $from
 * @property AddressDto $to
 * @property ParcelDto[] $places
 * @property float $assessedCost
 * @property float $codCost
 * @property bool $includeFees
 * @property string $pickupDate         Y-m-d ('' = текущая)
 * @property int[] $pickupTypes         1=забор от двери, 2=привоз на склад СД
 * @property int[] $deliveryTypes       1=до двери, 2=до ПВЗ
 * @property string[] $providerKeys
 * @property int[] $tariffIds
 * @property int $timeout               Ожидание ответа провайдеров, мс (0 = без ограничений)
 * @property bool $skipTariffRules
 * @property string $promoCode
 * @property string $customCode
 * @property int $pointInId             Склад СД для самозавоза (0 = не задан)
 * @property int $pointOutId            ПВЗ получателя (0 = не задан)
 * @property string $pickupTimeStart    ЧЧ:ММ
 * @property string $pickupTimeEnd      ЧЧ:ММ
 * @property string $deliveryTimeStart  ЧЧ:ММ
 * @property string $deliveryTimeEnd    ЧЧ:ММ
 * @property array<string,mixed> $extraParams  Например ['dpd.providerConnectId' => 1234]
 */
final class CalculationContext extends Resource
{
    use NormalizesValues;

    public static function make(array $data): static
    {
        $places = static::normalizePlaces((array)($data['places'] ?? []));

        return new static([
            'from'   => static::normalizeAddress($data['from'] ?? []),
            'to'     => static::normalizeAddress($data['to'] ?? []),
            'places' => $places,

            'assessedCost'    => round(max(0.0, (float)($data['assessedCost'] ?? 0)), 2),
            'codCost'         => round(max(0.0, (float)($data['codCost'] ?? 0)), 2),
            'includeFees'     => (bool)($data['includeFees'] ?? false),
            'pickupDate'      => trim((string)($data['pickupDate'] ?? '')),
            'pickupTypes'     => static::intList($data['pickupTypes'] ?? []),
            'deliveryTypes'   => static::intList($data['deliveryTypes'] ?? []),
            'providerKeys'    => static::stringList($data['providerKeys'] ?? []),
            'tariffIds'       => static::intList($data['tariffIds'] ?? []),
            'timeout'         => max(0, (int)($data['timeout'] ?? 0)),
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
     * @param array<string,mixed>|AddressDto $address
     */
    private static function normalizeAddress(mixed $address): AddressDto
    {
        if ($address instanceof AddressDto) {
            return $address;
        }
        return AddressDto::make((array)$address);
    }

    /**
     * @return ParcelDto[]
     */
    private static function normalizePlaces(array $places): array
    {
        $result = [];
        foreach ($places as $place) {
            if ($place instanceof ParcelDto) {
                $result[] = $place;
            } elseif (is_array($place)) {
                $result[] = ParcelDto::make($place);
            }
        }
        return $result;
    }
}
