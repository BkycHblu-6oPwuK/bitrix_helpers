<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Apiship\Dto\Concerns\NormalizesValues;
use Beeralex\Core\Http\Resources\Resource;

/**
 * Блок `cost` запроса создания заказа ApiShip (суммы в рублях).
 *
 * @property float $assessedCost Оценочная стоимость / сумма страховки. Обязательно. Пример: 100.12
 * @property float $codCost Сумма наложенного платежа с НДС (включает deliveryCost). Обязательно. Пример: 98.21
 * @property float|null $deliveryCost Стоимость доставки с получателя. Пример: 50.12
 * @property int|null $deliveryCostVat Ставка НДС доставки: -1 без НДС, 0, 5, 7, 10, 20, 22. Пример: 22
 * @property int|null $isDeliveryPayedByRecipient Кто платит доставку: 0 — отправитель, 1 — получатель
 * @property int|null $paymentMethod Способ оплаты: 1 наличные, 2 карта, 3 смешанная, 4 безналичная (по счёту)
 * @property array<int,array{deliveryCost:float,threshold:float}> $deliveryCostThresholds Пороги стоимости доставки при частичном выкупе
 */
final class CostDto extends Resource
{
    use NormalizesValues;

    public static function make(array $data): static
    {
        return new static([
            'assessedCost'               => (float)($data['assessedCost'] ?? 0),
            'codCost'                    => (float)($data['codCost'] ?? 0),
            'deliveryCost'               => static::nullableFloat($data['deliveryCost'] ?? null),
            'deliveryCostVat'            => static::nullableInt($data['deliveryCostVat'] ?? null),
            'isDeliveryPayedByRecipient' => static::nullableInt($data['isDeliveryPayedByRecipient'] ?? null),
            'paymentMethod'              => static::nullableInt($data['paymentMethod'] ?? null),
            'deliveryCostThresholds'     => array_values((array)($data['deliveryCostThresholds'] ?? [])),
        ]);
    }
}
