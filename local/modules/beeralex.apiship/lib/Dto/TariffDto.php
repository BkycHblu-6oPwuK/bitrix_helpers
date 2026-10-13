<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Core\Http\Resources\Resource;

/**
 * Нормализованный тариф расчёта доставки.
 *
 * @property string $providerKey
 * @property int $tariffId
 * @property string $tariffName
 * @property float $deliveryCost
 * @property int $daysMin
 * @property int $daysMax
 * @property int $deliveryType 1 — до двери, 2 — до ПВЗ
 * @property bool $isToPoint
 * @property array<int,string> $pointIds
 */
final class TariffDto extends Resource
{
    public static function make(array $data): static
    {
        $deliveryType = (int)($data['deliveryType'] ?? 0);

        return new static([
            'providerKey'  => (string)($data['providerKey'] ?? ''),
            'tariffId'     => (int)($data['tariffId'] ?? 0),
            'tariffName'   => (string)($data['tariffName'] ?? ''),
            'deliveryCost' => (float)($data['deliveryCost'] ?? 0),
            'daysMin'      => (int)($data['daysMin'] ?? 0),
            'daysMax'      => (int)($data['daysMax'] ?? 0),
            'deliveryType' => $deliveryType,
            'isToPoint'    => $deliveryType === 2,
            'pointIds'     => (array)($data['pointIds'] ?? []),
        ]);
    }
}
