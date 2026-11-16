<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Support;

use Apiship\Entity\Request\Part\Calculator\From;
use Apiship\Entity\Request\Part\Calculator\Place as CalculatorPlace;
use Apiship\Entity\Request\Part\Calculator\To;
use Beeralex\Apiship\Dto\CalculationContext;
use Beeralex\Apiship\Sdk\Entity\Request\CalculatorRequest;
use Beeralex\Apiship\Support\Concerns\RequestHydrator;

/**
 * Строит SDK-запрос {@see CalculatorRequest} из {@see CalculationContext}.
 * Транслирует все поля контекста в соответствующие сеттеры SDK-объекта.
 */
class CalculatorRequestBuilder
{
    use RequestHydrator;

    public function build(CalculationContext $context): CalculatorRequest
    {
        /**
         * @var CalculatorRequest $request
         */
        $request = (new CalculatorRequest())
            ->setFrom($this->hydrate(new From(), $this->address($context->from->toArray())))
            ->setTo($this->hydrate(new To(), $this->address($context->to->toArray())))
            ->setAssessedCost($context->assessedCost)
            ->setCodCost($context->codCost);

        foreach ($context->places as $place) {
            $request->addPlace(
                (new CalculatorPlace())
                    ->setWeight($place->weight)
                    ->setWidth($place->width)
                    ->setHeight($place->height)
                    ->setLength($place->length)
            );
        }

        if ($context->providerKeys !== []) {
            $request->setProviderKeys($context->providerKeys);
        }
        if ($context->tariffIds !== []) {
            $request->setTariffIds($context->tariffIds);
        }
        if ($context->deliveryTypes !== []) {
            $request->setDeliveryTypes($context->deliveryTypes);
        }
        if ($context->pickupTypes !== []) {
            $request->setPickupTypes($context->pickupTypes);
        }
        if ($context->pointInId > 0) {
            $request->setPointInId($context->pointInId);
        }
        if ($context->pointOutId > 0) {
            $request->setPointOutId($context->pointOutId);
        }
        if ($context->extraParams !== []) {
            $request->setExtraParams($context->extraParams);
        }
        if ($context->timeout > 0) {
            $request->setTimeout($context->timeout);
        }
        if ($context->includeFees) {
            $request->setIncludeFees(true);
        }
        if ($context->pickupDate !== '') {
            $request->setPickupDate($context->pickupDate);
        }
        if ($context->promoCode !== '') {
            $request->setPromoCode($context->promoCode);
        }
        if ($context->customCode !== '') {
            $request->setCustomCode($context->customCode);
        }
        if ($context->skipTariffRules) {
            $request->setSkipTariffRules(true);
        }
        if ($context->pickupTimeStart !== '') {
            $request->setPickupTimeStart($context->pickupTimeStart);
        }
        if ($context->pickupTimeEnd !== '') {
            $request->setPickupTimeEnd($context->pickupTimeEnd);
        }
        if ($context->deliveryTimeStart !== '') {
            $request->setDeliveryTimeStart($context->deliveryTimeStart);
        }
        if ($context->deliveryTimeEnd !== '') {
            $request->setDeliveryTimeEnd($context->deliveryTimeEnd);
        }

        return $request;
    }

    /**
     * В DTO почтовый индекс называется postIndex (как в заказе), в calculator-запросе — index.
     *
     * @param array<string,mixed> $address
     * @return array<string,mixed>
     */
    protected function address(array $address): array
    {
        if (isset($address['postIndex']) && !isset($address['index'])) {
            $address['index'] = $address['postIndex'];
        }
        unset($address['postIndex']);

        return $address;
    }
}
