<?php
require_once __DIR__ . '/lib/Enum/DIServiceKey.php';

use Beeralex\Catalog\Enum\DIServiceKey;
use Beeralex\Catalog\Location\BitrixLocationResolver;
use Beeralex\Catalog\Location\Contracts\BitrixLocationResolverContract;
use Beeralex\Catalog\Location\Contracts\LocationApiClientContract;
use Beeralex\Catalog\Location\Services\DadataService;
use Beeralex\Catalog\Repository\EmptyOffersRepository;
use Beeralex\Catalog\Repository\OffersRepository;
use Beeralex\Catalog\Repository\ProductsRepository;
use Beeralex\Catalog\Service\CatalogService;
use Beeralex\Core\Logger\LoggerFactoryContract;

return [
    'services' => [
        'value' => [
            LocationApiClientContract::class => [
                'className' => DadataService::class,
            ],
            BitrixLocationResolverContract::class => [
                'constructor' => static function () {
                    return new BitrixLocationResolver(service(LocationApiClientContract::class), service(LoggerFactoryContract::class)->channel('location'));
                }
            ],
            DIServiceKey::CATALOG_REPOSITORY->value => [
                'className' => ProductsRepository::class,
            ],
            DIServiceKey::OFFERS_REPOSITORY->value => [
                'className' => OffersRepository::class,
            ],
            DIServiceKey::EMPTY_OFFERS_REPOSITORY->value => [
                'className' => EmptyOffersRepository::class,
            ],
            CatalogService::class => [
                'constructor' => static function () {
                    return new CatalogService(service(DIServiceKey::CATALOG_REPOSITORY->value), service(DIServiceKey::OFFERS_REPOSITORY->value));
                }
            ]
        ],
        'readonly' => true,
    ]
];
