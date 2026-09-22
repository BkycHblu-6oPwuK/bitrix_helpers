<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Core\Http\Resources\Resource;

/**
 * Товарная позиция заказа.
 *
 * @property-read string $description
 * @property-read int $quantity
 * @property-read float $cost
 * @property-read float $assessedCost
 */
final class ItemDto extends Resource
{
    public function getDescription(): string
    {
        return (string)$this->getString('description');
    }

    public function getQuantity(): int
    {
        return (int)($this->getInt('quantity') ?? 1);
    }

    public function getCost(): float
    {
        return (float)$this->getFloat('cost');
    }

    public function getAssessedCost(): float
    {
        return (float)$this->getFloat('assessedCost');
    }

    public function getWeight(): int
    {
        return (int)$this->getInt('weight');
    }

    /**
     * @return array<string,mixed>
     */
    public function toApiShip(): array
    {
        return array_filter($this->toArray(), static fn($v) => $v !== null && $v !== '');
    }
}
