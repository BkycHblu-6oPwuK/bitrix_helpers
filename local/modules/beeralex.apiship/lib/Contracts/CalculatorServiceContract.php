<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Contracts;

use Beeralex\Apiship\Dto\CalculationContext;

interface CalculatorServiceContract
{
    /**
     * Расчёт стоимости и сроков доставки по всем доступным ТК.
     *
     * @return \Beeralex\Apiship\Dto\TariffDto[]
     */
    public function calculate(CalculationContext $context): array;
}
