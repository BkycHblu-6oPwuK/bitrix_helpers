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
 * Расчёт доставки для профиля
 */
class ProfileCalculatorService
{
    public function __construct(
        protected readonly CalculatorServiceContract $calculator,
        protected readonly CalculationContextFactory $contextFactory,
        protected readonly TariffSelector $selector,
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
