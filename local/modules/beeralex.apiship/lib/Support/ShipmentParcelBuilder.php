<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Delivery;

use Bitrix\Main\Loader;
use Bitrix\Sale\BasketItemBase;
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
final class ShipmentParcelBuilder
{
    private const MM_IN_CM = 10;

    /**
     * @param array{weight:int|float,width:int|float,height:int|float,length:int|float} $defaults
     *        вес по умолчанию в граммах, габариты по умолчанию в сантиметрах
     * @return array{
     *     weight:int, width:int, height:int, length:int,
     *     assessedCost:float, realDimensions:bool, itemsWithoutDimensions:int
     * } вес в граммах, габариты в сантиметрах, стоимость в рублях
     */
    public static function build(Shipment $shipment, array $defaults): array
    {
        $rows = static::collectRows($shipment);
        static::fillFromCatalog($rows);

        $boxLength = $boxWidth = $boxHeight = 0.0;
        $weight = 0.0;
        $cost = 0.0;
        $withDims = 0;
        $withoutDims = 0;

        foreach ($rows as $row) {
            $qty = $row['qty'];
            $weight += $row['weight'] * $qty;
            $cost += $row['price'] * $qty;

            if ($row['dims'] === null) {
                $withoutDims++;
                continue;
            }

            [$a, $b, $c] = $row['dims']; // мм, по убыванию
            $boxLength = max($boxLength, $a);
            $boxWidth = max($boxWidth, $b);
            $boxHeight += $c * ceil($qty);
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

    /**
     * @return array<int,array{item:BasketItemBase,qty:float,price:float,weight:float,dims:?array{0:float,1:float,2:float}}>
     */
    private static function collectRows(Shipment $shipment): array
    {
        $rows = [];

        foreach ($shipment->getShipmentItemCollection() as $shipmentItem) {
            $basketItem = $shipmentItem->getBasketItem();
            if (!$basketItem instanceof BasketItemBase) {
                continue;
            }

            $qty = (float)$shipmentItem->getQuantity();
            if ($qty <= 0) {
                continue;
            }

            $rows[] = [
                'item'   => $basketItem,
                'qty'    => $qty,
                'price'  => (float)$basketItem->getPrice(),
                'weight' => (float)$basketItem->getWeight(),
                'dims'   => static::readBasketDimensions($basketItem),
            ];
        }

        return $rows;
    }

    /**
     * Дозаполняет габариты/вес из каталога одним запросом для товаров, где в корзине их нет.
     *
     * @param array<int,array<string,mixed>> $rows
     */
    private static function fillFromCatalog(array &$rows): void
    {
        $missing = [];
        foreach ($rows as $index => $row) {
            if ($row['dims'] === null || $row['weight'] <= 0) {
                $productId = (int)$row['item']->getProductId();
                if ($productId > 0) {
                    $missing[$index] = $productId;
                }
            }
        }

        if ($missing === [] || !Loader::includeModule('catalog')) {
            return;
        }

        $catalog = [];
        $res = \Bitrix\Catalog\ProductTable::getList([
            'select' => ['ID', 'WEIGHT', 'WIDTH', 'HEIGHT', 'LENGTH'],
            'filter' => ['@ID' => array_values(array_unique($missing))],
        ]);
        while ($product = $res->fetch()) {
            $catalog[(int)$product['ID']] = [
                'weight' => (float)$product['WEIGHT'],
                'dims'   => static::normalizeDimensions($product),
            ];
        }

        foreach ($missing as $index => $productId) {
            if (!isset($catalog[$productId])) {
                continue;
            }
            if ($rows[$index]['dims'] === null) {
                $rows[$index]['dims'] = $catalog[$productId]['dims'];
            }
            if ($rows[$index]['weight'] <= 0) {
                $rows[$index]['weight'] = $catalog[$productId]['weight'];
            }
        }
    }

    /**
     * @return array{0:float,1:float,2:float}|null
     */
    private static function readBasketDimensions(BasketItemBase $item): ?array
    {
        $raw = $item->getField('DIMENSIONS');

        if (is_string($raw) && $raw !== '') {
            $raw = @unserialize($raw, ['allowed_classes' => false]);
        }

        return is_array($raw) ? static::normalizeDimensions($raw) : null;
    }

    /**
     * Принимает массив с ключами WIDTH/HEIGHT/LENGTH (мм). Возвращает стороны по убыванию
     * или null, если хотя бы одна сторона не задана.
     *
     * @return array{0:float,1:float,2:float}|null
     */
    private static function normalizeDimensions(array $raw): ?array
    {
        $sides = [
            (float)($raw['LENGTH'] ?? 0),
            (float)($raw['WIDTH'] ?? 0),
            (float)($raw['HEIGHT'] ?? 0),
        ];

        foreach ($sides as $side) {
            if ($side <= 0) {
                return null;
            }
        }

        rsort($sides);

        return [$sides[0], $sides[1], $sides[2]];
    }

    private static function mmToCm(float $mm): int
    {
        return max(1, (int)ceil($mm / static::MM_IN_CM));
    }
}
