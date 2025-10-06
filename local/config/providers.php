<?php
use App\Catalog\Location\LocationServiceProvider;
use App\Catalog\Repository\CatalogRepositoryProvider;
use App\Catalog\Type\CatalogTypesServiceProvider;
use Beeralex\Core\Logger\LoggerServiceProvider;
use App\Notification\NotificationServiceProvider;

return [
    LoggerServiceProvider::class,
    LocationServiceProvider::class,
    NotificationServiceProvider::class,
    CatalogTypesServiceProvider::class,
    CatalogRepositoryProvider::class
];