<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Support;

use Beeralex\Apiship\Support\Concerns\ConvertsUnits;
use Beeralex\Apiship\Dto\ShipmentLine;
use Bitrix\Sale\Shipment;

/**
 * Считает вес, габариты и оценочную стоимость отгрузки по реальным товарам.
 *
 * Источники (по приоритету):
 *  1. поля корзины: WEIGHT и DIMENSIONS (мм, хранятся в строке корзины при добавлении товара);
 *  2. каталог (b_catalog_product: WEIGHT, WIDTH, HEIGHT, LENGTH);
 *  3. значения по умолчанию из настроек модуля.
 *
 * Упаковка в одну коробку: каждый товар ориентируется наименьшей стороной вверх,
 * единицы одного товара и разные товары укладываются стопкой. Длина и ширина коробки —
 * максимумы по товарам, высота — сумма высот.
 */
class ShipmentParcelBuilder
{
    use ConvertsUnits;

    /** @param ShipmentLine[]|null $lines если уже прочитаны — повторно не читаем */
    public static function build(Shipment $shipment, array $defaults, ?array $lines = null): array
    {
        $lines ??= ShipmentLinesReader::read($shipment);

        $boxLength = $boxWidth = $boxHeight = 0.0;
        $weight = $cost = 0.0;
        $withDims = $withoutDims = 0;

        foreach ($lines as $line) {
            $weight += $line->weight * $line->quantity;
            $cost += $line->price * $line->quantity;

            if ($line->dimensionsMm === null) {
                $withoutDims++;
                continue;
            }

            [$a, $b, $c] = $line->dimensionsMm;
            $boxLength = max($boxLength, $a);
            $boxWidth = max($boxWidth, $b);
            $boxHeight += $c * ceil($line->quantity);
            $withDims++;
        }

        if ($weight <= 0) {
            $weight = (float)$shipment->getWeight();
        }

        $realDimensions = $withDims > 0;

        return [
            'weight'                 => (int)ceil($weight > 0 ? $weight : (float)$defaults['weight']),
            'length'                 => $realDimensions ? static::mmToCm($boxLength) : (int)ceil((float)$defaults['length']),
            'width'                  => $realDimensions ? static::mmToCm($boxWidth) : (int)ceil((float)$defaults['width']),
            'height'                 => $realDimensions ? static::mmToCm($boxHeight) : (int)ceil((float)$defaults['height']),
            'assessedCost'           => round($cost, 2),
            'realDimensions'         => $realDimensions,
            'itemsWithoutDimensions' => $withoutDims,
        ];
    }
}
