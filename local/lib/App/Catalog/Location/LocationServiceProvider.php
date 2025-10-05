<?php

namespace App\Catalog\Location;

use App\Catalog\Location\Contracts\BitrixLocationResolverContract;
use App\Catalog\Location\Contracts\LocationApiClientContract;
use App\Catalog\Location\Services\DadataService;

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
