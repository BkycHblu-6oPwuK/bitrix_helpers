<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Core\Http\Resources\Resource;

/**
 * Входные данные для расчёта стоимости доставки.
 *
 * Собирается вызывающим кодом (обработчиком доставки/сервисом заказа) и передаётся
 * в CalculatorService::calculate(). from/to — AddressDto (as array), places — PlaceDto[].
 *
 * @property array $from AddressDto as array
 * @property array $to AddressDto as array
 * @property array<int,array<string,mixed>> $places PlaceDto[] as array
 * @property float $assessedCost
 * @property float $codCost
 * @property array<int,string> $providerKeys
 */
final class CalculationRequestDto extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'from'         => (array)($data['from'] ?? []),
            'to'           => (array)($data['to'] ?? []),
            'places'       => (array)($data['places'] ?? []),
            'assessedCost' => (float)($data['assessedCost'] ?? 0),
            'codCost'      => (float)($data['codCost'] ?? 0),
            'providerKeys' => (array)($data['providerKeys'] ?? []),
        ]);
    }
}
