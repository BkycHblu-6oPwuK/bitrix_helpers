<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Core\Http\Resources\Resource;

/**
 * Грузовое место (габариты + вес).
 *
 * @property-read int $weight Вес в граммах
 * @property-read int $width  Ширина в см
 * @property-read int $height Высота в см
 * @property-read int $length Длина в см
 */
final class PlaceDto extends Resource
{
    public function getWeight(): int
    {
        return (int)$this->getInt('weight');
    }

    public function getWidth(): int
    {
        return (int)$this->getInt('width');
    }

    public function getHeight(): int
    {
        return (int)$this->getInt('height');
    }

    public function getLength(): int
    {
        return (int)$this->getInt('length');
    }

    /**
     * @return array<string,int>
     */
    public function toApiShip(): array
    {
        return [
            'weight' => $this->getWeight(),
            'width'  => $this->getWidth(),
            'height' => $this->getHeight(),
            'length' => $this->getLength(),
        ];
    }
}
