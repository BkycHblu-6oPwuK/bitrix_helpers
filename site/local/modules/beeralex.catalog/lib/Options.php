<?php
declare(strict_types=1);

namespace Beeralex\Catalog;

use Beeralex\Core\Config\AbstractOptions;

final class Options extends AbstractOptions
{
    public readonly bool $minPriceIsDiscount;

    protected function mapOptions(array $options): void
    {
        $this->minPriceIsDiscount = $options['BEERALEX_CATALOG_MIN_PRICE_IS_DISCOUNT'] === 'Y';
    }

    public function getModuleId(): string
    {
        return 'beeralex.catalog';
    }
}