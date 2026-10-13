<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Sdk\Entity\Request\Part;

use Apiship\Entity\Request\Part\Orders\Cost as BaseCost;

/**
 * Стоимость заказа с порогами доставки, которых нет в vendor SDK.
 */
class Cost extends BaseCost
{
    /** @var array<int,array{deliveryCost:float,threshold:float}>|null */
    protected $deliveryCostThresholds;

    /**
     * @param array<int,array{deliveryCost:float,threshold:float}> $deliveryCostThresholds
     */
    public function setDeliveryCostThresholds($deliveryCostThresholds): self
    {
        $this->deliveryCostThresholds = $deliveryCostThresholds;
        return $this;
    }
}
