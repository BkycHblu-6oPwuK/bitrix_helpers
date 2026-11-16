<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Sdk\Entity\Request;

use Apiship\Entity\Request\CalculatorRequest as BaseCalculatorRequest;

/**
 * Запрос расчёта с интервалами забора/доставки, которых нет в vendor SDK
 * (OpenAPI: POST /calculator). asArray() подхватывает public-свойства потомка.
 */
class CalculatorRequest extends BaseCalculatorRequest
{
    /** @var string|null Начало желаемого интервала забора, H:i */
    public $pickupTimeStart;

    /** @var string|null Конец желаемого интервала забора, H:i */
    public $pickupTimeEnd;

    /** @var string|null Начало желаемого интервала доставки, H:i */
    public $deliveryTimeStart;

    /** @var string|null Конец желаемого интервала доставки, H:i */
    public $deliveryTimeEnd;

    public function setPickupTimeStart($value): self
    {
        $this->pickupTimeStart = $value;
        return $this;
    }

    public function setPickupTimeEnd($value): self
    {
        $this->pickupTimeEnd = $value;
        return $this;
    }

    public function setDeliveryTimeStart($value): self
    {
        $this->deliveryTimeStart = $value;
        return $this;
    }

    public function setDeliveryTimeEnd($value): self
    {
        $this->deliveryTimeEnd = $value;
        return $this;
    }
}
