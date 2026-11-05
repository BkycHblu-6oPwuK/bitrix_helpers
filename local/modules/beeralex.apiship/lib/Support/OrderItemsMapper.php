<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Support;

use Beeralex\Apiship\Options;
use Beeralex\Apiship\Support\Concerns\ConvertsUnits;
use Bitrix\Main\Config\Option;

class OrderItemsMapper
{
    use ConvertsUnits;

    public function __construct(private readonly Options $options) {}

    /**
     * @param ShipmentLine[] $lines
     * @param bool $isCod items.cost = реальная цена (COD) или 0 (предоплата, страховка через assessedCost)
     * @return array<int,array<string,mixed>>
     */
    public function map(array $lines, bool $isCod): array
    {
        $seller = $this->options->sender;
        $items = [];

        foreach ($lines as $line) {
            $basketItem = $line->basketItem;
            $dims = $line->dimensionsMm;
            $vat = $basketItem->getVatRate();

            $items[] = [
                'description'  => (string)$basketItem->getField('NAME'),
                'articul'      => (string)$basketItem->getField('PRODUCT_XML_ID'),
                'quantity'     => (int)$line->quantity,
                'cost'         => $isCod ? $line->price : 0.0,
                'assessedCost' => $line->price,
                'costVat'      => static::vatToPercent($vat === null ? null : (float)$vat),
                'weight'       => (int)$line->weight,
                'length'       => $dims !== null ? static::mmToCm($dims[0]) : null,
                'width'        => $dims !== null ? static::mmToCm($dims[1]) : null,
                'height'       => $dims !== null ? static::mmToCm($dims[2]) : null,
                'url'          => $this->itemUrl((string)$basketItem->getField('DETAIL_PAGE_URL')),
                'companyName'  => $seller['companyName'] ?? null,
                'companyInn'   => $seller['companyInn'] ?? null,
                'companyPhone' => $seller['phone'] ?? null,
            ];
        }

        return $items;
    }

    protected function itemUrl(string $path): ?string
    {
        if ($path === '') {
            return null;
        }
        if (str_starts_with($path, 'http')) {
            return $path;
        }
        $host = (string)Option::get('main', 'server_name', '');

        return $host !== '' ? 'https://' . $host . $path : null;
    }
}
