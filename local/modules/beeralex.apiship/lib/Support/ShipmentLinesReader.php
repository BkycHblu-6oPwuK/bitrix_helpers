<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Support;

use Beeralex\Apiship\Dto\ShipmentLine;
use Bitrix\Sale\BasketItemBase;
use Bitrix\Main\Loader;
use Bitrix\Sale\Shipment;

class ShipmentLinesReader
{
    /** @return ShipmentLine[] */
    public static function read(Shipment $shipment): array
    {
        $rows = static::collectRows($shipment);
        static::fillFromCatalog($rows);

        return array_map(
            static fn(array $r) => new ShipmentLine($r['item'], $r['qty'], $r['price'], $r['weight'], $r['dims']),
            $rows
        );
    }

    /**
     * @return array<int,array{item:BasketItemBase,qty:float,price:float,weight:float,dims:?array{0:float,1:float,2:float}}>
     */
    protected static function collectRows(Shipment $shipment): array
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
    protected static function fillFromCatalog(array &$rows): void
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
    protected static function readBasketDimensions(BasketItemBase $item): ?array
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
    protected static function normalizeDimensions(array $raw): ?array
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
}
