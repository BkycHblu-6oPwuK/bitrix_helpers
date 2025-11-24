<?
namespace Beeralex\Catalog\Dto;

class PriceDTO
{
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly float $value,
        public readonly string $formatted,
        public readonly bool $isBase,
        public readonly bool $isCurrent,
        public readonly float $discountPercent,
    )
    {}
}