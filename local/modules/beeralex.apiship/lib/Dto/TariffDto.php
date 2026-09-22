<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Core\Http\Resources\Resource;

/**
 * Нормализованный тариф расчёта доставки.
 *
 * @property-read string $providerKey
 * @property-read int $tariffId
 * @property-read string $tariffName
 * @property-read float $deliveryCost
 * @property-read int $daysMin
 * @property-read int $daysMax
 * @property-read int $deliveryType 1 — до двери, 2 — до ПВЗ
 */
final class TariffDto extends Resource
{
    public function getProviderKey(): string
    {
        return (string)$this->getString('providerKey');
    }

    public function getTariffId(): int
    {
        return (int)$this->getInt('tariffId');
    }

    public function getTariffName(): string
    {
        return (string)$this->getString('tariffName');
    }

    public function getDeliveryCost(): float
    {
        return (float)$this->getFloat('deliveryCost');
    }

    public function getDaysMin(): int
    {
        return (int)$this->getInt('daysMin');
    }

    public function getDaysMax(): int
    {
        return (int)$this->getInt('daysMax');
    }

    public function getDeliveryType(): int
    {
        return (int)$this->getInt('deliveryType');
    }

    public function isToPoint(): bool
    {
        return $this->getDeliveryType() === 2;
    }
}
