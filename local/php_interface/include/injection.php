<?php

use App\Catalog\Location\LocationServiceProvider;
use App\Catalog\Repository\CatalogRepositoryProvider;
use App\Catalog\Type\CatalogTypesServiceProvider;
use Itb\Core\Logger\LoggerServiceProvider;
use App\Notification\NotificationServiceProvider;

/**
 * @var \Itb\Core\DI\AbstractServiceProvider[]
 */
$providers = [
    LoggerServiceProvider::class,
    LocationServiceProvider::class,
    NotificationServiceProvider::class,
    CatalogTypesServiceProvider::class,
    CatalogRepositoryProvider::class
];

foreach ($providers as $provider) {
    (new $provider)->register();
}
