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
     */
    public function calculate(CalculationRequestDto $request): array
    {
        $calculatorRequest = $this->buildRequest($request);

        try {
            $response = $this->clientFactory->getClient()->calculator()->calculate($calculatorRequest);
        } catch (ResponseException $e) {
            throw new ApiShipException(
                $e->getErrorMessage() ?: $e->getMessage(),
                (int)$e->getCode(),
                $e
            );
        }

        return $this->normalizeResponse($response);
    }

    private function buildRequest(CalculationRequestDto $request): CalculatorRequest
    {
        $calculatorRequest = (new CalculatorRequest())
            ->setFrom($this->hydrate(new From(), $request->getFrom()))
            ->setTo($this->hydrate(new To(), $request->getTo()))
            ->setAssessedCost($request->getAssessedCost())
            ->setCodCost($request->getCodCost());

        foreach ($request->getPlaces() as $place) {
            $calculatorRequest->addPlace(
                (new CalculatorPlace())
                    ->setWeight((int)($place['weight'] ?? 0))
                    ->setWidth((int)($place['width'] ?? 0))
                    ->setHeight((int)($place['height'] ?? 0))
                    ->setLength((int)($place['length'] ?? 0))
            );
        }

        if ($providerKeys = $request->getProviderKeys()) {
            $calculatorRequest->setProviderKeys($providerKeys);
        }

        return $calculatorRequest;
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
