<?php

use Itb\Catalog\Location\LocationServiceProvider;
use Itb\Catalog\Types\CatalogTypesServiceProvider;
use Itb\Core\Logger\LoggerServiceProvider;
use Itb\Notification\NotificationServiceProvider;

/**
 * @var \Itb\Core\DI\AbstractServiceProvider[]
 */
$providers = [
    LoggerServiceProvider::class,
    LocationServiceProvider::class,
    NotificationServiceProvider::class,
    CatalogTypesServiceProvider::class
];

foreach ($providers as $provider) {
    (new $provider)->register();
}
