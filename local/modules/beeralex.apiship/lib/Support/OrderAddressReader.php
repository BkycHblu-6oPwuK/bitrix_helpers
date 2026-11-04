<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Support;

use Beeralex\Apiship\Options;
use Beeralex\Apiship\Support\Concerns\ReadsOrderProperties;
use Bitrix\Sale\Order;

final class OrderAddressReader
{
    use ReadsOrderProperties;

    public function __construct(private readonly Options $options) {}

    /** @return array{city:?string,cityGuid:?string,addressString:?string,countryCode:string} */
    public function read(Order $order): array
    {
        $p = $order->getPropertyCollection();

        return [
            'city'          => $this->propertyValue($p, $this->options->propCity),
            'cityGuid'      => $this->propertyValue($p, $this->options->propCityGuid),
            'addressString' => $this->propertyValue($p, $this->options->propAddress),
            'countryCode'   => $this->propertyValue($p, $this->options->propCountryCode) ?: 'RU',
        ];
    }
}
