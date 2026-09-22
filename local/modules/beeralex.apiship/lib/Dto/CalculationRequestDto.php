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
 * @property-read array $from
 * @property-read array $to
 * @property-read array $places
 */
final class CalculationRequestDto extends Resource
{
    public function getFrom(): array
    {
        return (array)($this->get('from') ?? []);
    }

    public function getTo(): array
    {
        return (array)($this->get('to') ?? []);
    }

    /** @return array<int,array<string,mixed>> */
    public function getPlaces(): array
    {
        return (array)($this->get('places') ?? []);
    }

    public function getAssessedCost(): float
    {
        return (float)$this->getFloat('assessedCost');
    }

    public function getCodCost(): float
    {
        return (float)$this->getFloat('codCost');
    }

    /** @return array<int,string> */
    public function getProviderKeys(): array
    {
        return (array)($this->get('providerKeys') ?? []);
    }
}
