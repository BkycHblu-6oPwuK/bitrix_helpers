<?php

namespace Itb\Catalog\Location;

use Itb\Catalog\Location\Contracts\BitrixLocationResolverContract;
use Itb\Catalog\Location\Contracts\LocationApiClientContract;
use Itb\Catalog\Location\Services\DadataService;

class LocationServiceProvider extends \Itb\Core\DI\AbstractServiceProvider
{
    protected function registerServices(): void
    {
        $this->bind(LocationApiClientContract::class, DadataService::class);
        $this->bind(BitrixLocationResolverContract::class, BitrixLocationResolver::class, fn() => [
            $this->locator->get(LocationApiClientContract::class),
            $this->locator->get(\Itb\Core\Logger\LoggerFactoryContract::class)->channel('location'),
        ]);
    }
}
