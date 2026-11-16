<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Apiship\Support\Concerns\Arrayable;
use Beeralex\Apiship\Support\Concerns\NormalizesValues;

/**
 * Блок `cost` запроса создания заказа ApiShip (суммы в рублях).
 */
class CostDto
{
    use Arrayable;
    use NormalizesValues;

    public function __construct(
        /** Оценочная стоимость / сумма страховки. Обязательно. Пример: 100.12 */
        public readonly float $assessedCost,
        /** Сумма наложенного платежа с НДС (включает deliveryCost). Обязательно. Пример: 98.21 */
        public readonly float $codCost,
        /** Стоимость доставки с получателя. Пример: 50.12 */
        public readonly ?float $deliveryCost,
        /** Ставка НДС доставки: -1 без НДС, 0, 5, 7, 10, 20, 22. Пример: 22 */
        public readonly ?int $deliveryCostVat,
        /** Кто платит доставку: false — отправитель, true — получатель (в OpenAPI — boolean) */
        public readonly ?bool $isDeliveryPayedByRecipient,
        /** Способ оплаты: 1 наличные, 2 карта, 3 смешанная, 4 безналичная (по счёту) */
        public readonly ?int $paymentMethod,
        /** Пороги стоимости доставки при частичном выкупе */
        public readonly array $deliveryCostThresholds,
    ) {}

    public static function make(array $data): static
    {
        return new static(
            assessedCost: (float)($data['assessedCost'] ?? 0),
            codCost: (float)($data['codCost'] ?? 0),
            deliveryCost: static::nullableFloat($data['deliveryCost'] ?? null),
            deliveryCostVat: static::nullableInt($data['deliveryCostVat'] ?? null),
            isDeliveryPayedByRecipient: isset($data['isDeliveryPayedByRecipient']) && $data['isDeliveryPayedByRecipient'] !== ''
                ? (bool)$data['isDeliveryPayedByRecipient']
                : null,
            paymentMethod: static::nullableInt($data['paymentMethod'] ?? null),
            deliveryCostThresholds: array_values((array)($data['deliveryCostThresholds'] ?? [])),
        );
    }
}
