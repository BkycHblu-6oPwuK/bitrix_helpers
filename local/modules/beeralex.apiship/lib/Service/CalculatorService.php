<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Service;

use Apiship\Entity\Request\CalculatorRequest;
use Apiship\Entity\Request\Part\Calculator\From;
use Apiship\Entity\Request\Part\Calculator\Place as CalculatorPlace;
use Apiship\Entity\Request\Part\Calculator\To;
use Apiship\Entity\Response\CalculatorResponse;
use Apiship\Entity\Response\Part\Calculator\CalculatorItem;
use Apiship\Exception\ResponseException;
use Beeralex\Apiship\Client\ClientFactory;
use Beeralex\Apiship\Contracts\CalculatorServiceContract;
use Beeralex\Apiship\Dto\CalculationRequestDto;
use Beeralex\Apiship\Dto\TariffDto;
use Beeralex\Apiship\Exception\ApiShipException;
use Beeralex\Apiship\Options;
use Beeralex\Apiship\Service\Support\RequestHydrator;
use Bitrix\Main\Data\Cache;

/**
 * Расчёт стоимости и сроков доставки через ApiShip.
 */
final class CalculatorService implements CalculatorServiceContract
{
    use RequestHydrator;

    public function __construct(
        private readonly ClientFactory $clientFactory,
        private readonly Options $options
    ) {}

    /**
     * @return TariffDto[]
     * @throws ApiShipException
     */
    public function calculate(CalculationRequestDto $request): array
    {
        $cache = null;
        if ($this->options->cacheEnabled) {
            $cache = Cache::createInstance();
            $cacheKey = 'calc_' . md5(serialize($request));
            if ($cache->initCache($this->options->cacheTtl, $cacheKey, '/beeralex/apiship/calculator')) {
                return $cache->getVars();
            }
            $cache->startDataCache();
        }

        $sdkRequest = $this->buildRequest($request);

        try {
            $response = $this->clientFactory->getClient()->calculator()->calculate($sdkRequest);
        } catch (ResponseException $e) {
            throw new ApiShipException(
                $e->getErrorMessage() ?: $e->getMessage(),
                (int)$e->getCode(),
                $e
            );
        } catch (\Throwable $e) {
            throw new ApiShipException($e->getMessage(), 0, $e);
        }

        $result = $this->normalizeResponse($response);

        if ($cache !== null) {
            $cache->endDataCache($result);
        }

        return $result;
    }

    private function buildRequest(CalculationRequestDto $request): CalculatorRequest
    {
        $sdkRequest = (new CalculatorRequest())
            ->setFrom($this->hydrate(new From(), $request->from))
            ->setTo($this->hydrate(new To(), $request->to))
            ->setAssessedCost($request->assessedCost)
            ->setCodCost($request->codCost);

        foreach ($request->places as $place) {
            $sdkRequest->addPlace(
                (new CalculatorPlace())
                    ->setWeight((int)($place['weight'] ?? 0))
                    ->setWidth((int)($place['width'] ?? 0))
                    ->setHeight((int)($place['height'] ?? 0))
                    ->setLength((int)($place['length'] ?? 0))
            );
        }

        if ($request->providerKeys !== []) {
            $sdkRequest->setProviderKeys($request->providerKeys);
        }
        if ($request->tariffIds !== []) {
            $sdkRequest->setTariffIds($request->tariffIds);
        }
        if ($request->deliveryTypes !== []) {
            $sdkRequest->setDeliveryTypes($request->deliveryTypes);
        }
        if ($request->pointInId > 0) {
            $sdkRequest->setPointInId($request->pointInId);
        }
        if ($request->pointOutId > 0) {
            $sdkRequest->setPointOutId($request->pointOutId);
        }

        return $sdkRequest;
    }

    /**
     * @return TariffDto[]
     */
    private function normalizeResponse(CalculatorResponse $response): array
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
    private function normalizeItem(CalculatorItem $item, int $deliveryType): array
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
