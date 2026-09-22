<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Contracts;

use Beeralex\Apiship\Dto\CalculationRequestDto;

interface CalculatorServiceContract
{
    /**
     * Расчёт стоимости и сроков доставки по всем доступным ТК.
     *
     * @return \Beeralex\Apiship\Dto\TariffDto[]
     */
    public function calculate(CalculationRequestDto $request): array;
}
