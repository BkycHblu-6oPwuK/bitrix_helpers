<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Service;

use Apiship\Entity\Response\CalculatorResponse;
use Apiship\Entity\Response\Part\Calculator\CalculatorItem;
use Apiship\Exception\ResponseException;
use Beeralex\Apiship\Support\CalculatorRequestBuilder;
use Beeralex\Apiship\Support\ClientFactory;
use Beeralex\Apiship\Contracts\CalculatorServiceContract;
use Beeralex\Apiship\Dto\CalculationContext;
use Beeralex\Apiship\Dto\TariffDto;
use Beeralex\Apiship\Exception\ApiShipException;
use Beeralex\Apiship\Options;
use Bitrix\Main\Data\Cache;

/**
 * Расчёт стоимости и сроков доставки через ApiShip.
 */
class CalculatorService implements CalculatorServiceContract
{
    public function __construct(
        protected readonly ClientFactory $clientFactory,
        protected readonly Options $options,
        protected readonly CalculatorRequestBuilder $requestBuilder
    ) {}

    /**
     * @return TariffDto[]
     * @throws ApiShipException
     */
    public function calculate(CalculationContext $context): array
    {
        $sdkRequest = $this->requestBuilder->build($context);

        $cache = null;
        if ($this->options->cacheEnabled) {
            $cache = Cache::createInstance();
            // v1 — поднимать при изменении TariffDto
            $cacheKey = 'calc_v1_' . md5(serialize($context));
            if ($cache->initCache($this->options->cacheTtl, $cacheKey, '/beeralex/apiship/calculator')) {
                return $cache->getVars();
            }
            $cache->startDataCache();
        }

        try {
            $response = $this->clientFactory->getClient()->calculator()->calculate($sdkRequest);
        } catch (ResponseException $e) {
            $cache?->abortDataCache();
            throw new ApiShipException(
                $e->getErrorMessage() ?: $e->getMessage(),
                (int)$e->getCode(),
                $e
            );
        } catch (\Throwable $e) {
            $cache?->abortDataCache();
            throw new ApiShipException($e->getMessage(), 0, $e);
        }

        $result = $this->normalizeResponse($response);

        if ($cache !== null) {
            if ($result === []) {
                $cache->abortDataCache();
            } else {
                $cache->endDataCache($result);
            }
        }

        return $result;
    }

    /**
     * @return TariffDto[]
     */
    protected function normalizeResponse(CalculatorResponse $response): array
    {
        $result = [];

        foreach ($response->getDeliveryToDoor() ?? [] as $item) {
            $result = array_merge($result, $this->normalizeItem($item, 1));
        }

        foreach ($response->getDeliveryToPoint() ?? [] as $item) {
            $result = array_merge($result, $this->normalizeItem($item, 2));
        }

        return $result;
    }

    /**
     * @return TariffDto[]
     */
    protected function normalizeItem(CalculatorItem $item, int $deliveryType): array
    {
        $result = [];

        foreach ($item->getTariffs() ?? [] as $tariff) {
            $result[] = TariffDto::make([
                'providerKey'  => $item->getProviderKey(),
                'tariffId'     => $tariff->getTariffId(),
                'tariffName'   => $tariff->getTariffName(),
                'deliveryCost' => $tariff->getDeliveryCost(),
                'daysMin'      => $tariff->getDaysMin(),
                'daysMax'      => $tariff->getDaysMax(),
                'deliveryType' => $deliveryType,
                'pointIds'     => $tariff->getPointIds(),
            ]);
        }

        return $result;
    }
}
