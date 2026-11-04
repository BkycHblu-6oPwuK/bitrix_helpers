<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Support;

use Beeralex\Apiship\Dto\CalculationContext;
use Beeralex\Apiship\Enum\PickupType;
use Beeralex\Apiship\Options;
use Bitrix\Sale\Shipment;

final class CalculationContextFactory
{
    public function __construct(
        private readonly Options $options,
        private readonly OrderAddressReader $addressReader,
    ) {}

    /** @throws \InvalidArgumentException если для запроса не хватает данных */
    public function create(Shipment $shipment, ProfileConfig $config): CalculationContext
    {
        $order = $shipment->getOrder();
        $to = $order === null
            ? []
            : array_filter($this->addressReader->read($order), static fn($v) => $v !== null && $v !== '');

        $parcel = ShipmentParcelBuilder::build($shipment, [
            'weight' => $this->options->defaultWeight,
            'width'  => $this->options->defaultWidth,
            'height' => $this->options->defaultHeight,
            'length' => $this->options->defaultLength,
        ]);

        // у ТК может быть несколько подключений — считаем по выбранному
        $extraParams = [];
        if ($config->providerKey !== '' && ctype_digit($config->connectId) && (int)$config->connectId > 0) {
            $extraParams[$config->providerKey . '.providerConnectId'] = (int)$config->connectId;
        }

        $sender = $this->options->sender;
        $selfDelivery = $config->pickupType === PickupType::SELF_DELIVERY->value;

        $context = CalculationContext::make([
            'from' => [
                'city'          => $sender['city'] ?? null,
                'cityGuid'      => $sender['cityGuid'] ?? null,
                'addressString' => $sender['addressString'] ?? null,
                'countryCode'   => $sender['countryCode'] ?? 'RU',
            ],
            'to'     => $to,
            'places' => [[
                'weight' => $parcel['weight'],
                'width'  => $parcel['width'],
                'height' => $parcel['height'],
                'length' => $parcel['length'],
            ]],
            'assessedCost'  => $parcel['assessedCost'],
            'providerKeys'  => $config->providerKey !== '' ? [$config->providerKey] : [],
            'tariffIds'     => $config->tariffIds,
            'deliveryTypes' => $config->deliveryType > 0 ? [$config->deliveryType] : [],
            'pickupTypes'   => $config->pickupType > 0 ? [$config->pickupType] : [],
            // склад СД имеет смысл только при самозавозе
            'pointInId'     => $selfDelivery ? $config->pointInId : 0,
            'extraParams'   => $extraParams,
        ]);

        $errors = CalculationValidator::validate($context);
        if ($errors !== []) {
            throw new \InvalidArgumentException(implode('; ', $errors));
        }

        return $context;
    }
}
