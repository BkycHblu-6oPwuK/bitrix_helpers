<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Apiship\Support\Concerns\Arrayable;
use Beeralex\Apiship\Support\Concerns\NormalizesValues;

/**
 * Контекст запроса расчёта стоимости доставки — только данные и нормализация типов.
 * Без валидации, без формирования SDK-запроса.
 *
 * Единицы: вес — граммы, габариты — сантиметры, деньги — рубли.
 */
class CalculationContext
{
    use Arrayable;
    use NormalizesValues;

    public function __construct(
        public readonly AddressDto $from,
        public readonly AddressDto $to,
        /** @var ParcelDto[] */
        public readonly array $places,
        public readonly float $assessedCost,
        public readonly float $codCost,
        public readonly bool $includeFees,
        /** Y-m-d ('' = текущая) */
        public readonly string $pickupDate,
        /** 1=забор от двери, 2=привоз на склад СД */
        public readonly array $pickupTypes,
        /** 1=до двери, 2=до ПВЗ */
        public readonly array $deliveryTypes,
        public readonly array $providerKeys,
        public readonly array $tariffIds,
        /** Ожидание ответа провайдеров, мс (0 = без ограничений) */
        public readonly int $timeout,
        public readonly bool $skipTariffRules,
        public readonly string $promoCode,
        public readonly string $customCode,
        /** Склад СД для самозавоза (0 = не задан) */
        public readonly int $pointInId,
        /** ПВЗ получателя (0 = не задан) */
        public readonly int $pointOutId,
        /** ЧЧ:ММ */
        public readonly string $pickupTimeStart,
        /** ЧЧ:ММ */
        public readonly string $pickupTimeEnd,
        /** ЧЧ:ММ */
        public readonly string $deliveryTimeStart,
        /** ЧЧ:ММ */
        public readonly string $deliveryTimeEnd,
        /** Например ['dpd.providerConnectId' => 1234] */
        public readonly array $extraParams,
    ) {}

    public static function make(array $data): static
    {
        $places = static::normalizePlaces((array)($data['places'] ?? []));

        return new static(
            from: static::normalizeAddress($data['from'] ?? []),
            to: static::normalizeAddress($data['to'] ?? []),
            places: $places,

            assessedCost: round(max(0.0, (float)($data['assessedCost'] ?? 0)), 2),
            codCost: round(max(0.0, (float)($data['codCost'] ?? 0)), 2),
            includeFees: (bool)($data['includeFees'] ?? false),
            pickupDate: trim((string)($data['pickupDate'] ?? '')),
            pickupTypes: static::intList($data['pickupTypes'] ?? []),
            deliveryTypes: static::intList($data['deliveryTypes'] ?? []),
            providerKeys: static::stringList($data['providerKeys'] ?? []),
            tariffIds: static::intList($data['tariffIds'] ?? []),
            timeout: max(0, (int)($data['timeout'] ?? 0)),
            skipTariffRules: (bool)($data['skipTariffRules'] ?? false),
            promoCode: trim((string)($data['promoCode'] ?? '')),
            customCode: trim((string)($data['customCode'] ?? '')),
            pointInId: max(0, (int)($data['pointInId'] ?? 0)),
            pointOutId: max(0, (int)($data['pointOutId'] ?? 0)),
            pickupTimeStart: trim((string)($data['pickupTimeStart'] ?? '')),
            pickupTimeEnd: trim((string)($data['pickupTimeEnd'] ?? '')),
            deliveryTimeStart: trim((string)($data['deliveryTimeStart'] ?? '')),
            deliveryTimeEnd: trim((string)($data['deliveryTimeEnd'] ?? '')),
            extraParams: array_filter(
                (array)($data['extraParams'] ?? []),
                static fn($v) => $v !== null && $v !== ''
            ),
        );
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
