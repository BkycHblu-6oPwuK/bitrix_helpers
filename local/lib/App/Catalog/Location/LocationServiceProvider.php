<?php

namespace App\Catalog\Location;

use App\Catalog\Location\Contracts\BitrixLocationResolverContract;
use App\Catalog\Location\Contracts\LocationApiClientContract;
use App\Catalog\Location\Services\DadataService;

class LocationServiceProvider extends \Beeralex\Core\DI\AbstractServiceProvider
{
    protected function registerServices(): void
    {
        $this->bind(LocationApiClientContract::class, DadataService::class);
        $this->bind(BitrixLocationResolverContract::class, BitrixLocationResolver::class, fn() => [
            $this->locator->get(LocationApiClientContract::class),
            $this->locator->get(\Beeralex\Core\Logger\LoggerFactoryContract::class)->channel('location'),
        ]);
    }
}
