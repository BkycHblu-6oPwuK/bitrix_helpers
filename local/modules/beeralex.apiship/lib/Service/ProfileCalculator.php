<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Service;

use Beeralex\Apiship\Exception\ApiShipProfileCalculationException;
use Beeralex\Apiship\Contracts\CalculatorServiceContract;
use Beeralex\Apiship\Dto\TariffDto;
use Beeralex\Apiship\Support\CalculationContextFactory;
use Beeralex\Apiship\Support\ProfileConfig;
use Beeralex\Apiship\Support\TariffSelector;
use Bitrix\Sale\Shipment;

/**
 * Расчёт доставки для профиля: контекст → API → выбор тарифа.
 * Не знает про CalculationResult Bitrix и не пишет в заказ.
 */
final class ProfileCalculator
{
    public function __construct(
        private readonly CalculatorServiceContract $calculator,
        private readonly CalculationContextFactory $contextFactory,
        private readonly TariffSelector $selector,
    ) {}

    /**
     * @throws ApiShipProfileCalculationException
     */
    public function calculate(Shipment $shipment, ProfileConfig $config): ?TariffDto
    {
        try {
            $context = $this->contextFactory->create($shipment, $config);
            $tariffs = $this->calculator->calculate($context);
        } catch (\Throwable $e) {
            throw new ApiShipProfileCalculationException($e->getMessage(), 0, $e);
        }

        $tariff = $this->selector->pickCheapest($tariffs, $config->tariffIds);

        return $tariff;
    }
}
