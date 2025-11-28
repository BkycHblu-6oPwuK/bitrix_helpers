<?php
require_once __DIR__ . '/lib/Enum/DIServiceKey.php';

use Beeralex\Catalog\Enum\DIServiceKey;
use Beeralex\Catalog\Helper\OrderService;
use Beeralex\Catalog\Service\SortingService;
use Beeralex\Catalog\Location\BitrixLocationResolver;
use Beeralex\Catalog\Location\Contracts\BitrixLocationResolverContract;
use Beeralex\Catalog\Location\Contracts\LocationApiClientContract;
use Beeralex\Catalog\Location\Service\DadataService;
use Beeralex\Catalog\Repository\CatalogViewedProductRepository;
use Beeralex\Catalog\Repository\EmptyOffersRepository;
use Beeralex\Catalog\Repository\OffersRepository;
use Beeralex\Catalog\Repository\PriceTypeRepository;
use Beeralex\Catalog\Repository\ProductsRepository;
use Beeralex\Catalog\Service\CatalogService;
use Bitrix\Main\Context;
use Beeralex\Catalog\Options;
use Beeralex\Catalog\Repository\FuserRepository;
use Beeralex\Catalog\Repository\PersonTypeRepository;
use Beeralex\Catalog\Repository\PriceRepository;
use Beeralex\Catalog\Repository\SortingRepository;
use Beeralex\Catalog\Repository\StoreRepository;
use Beeralex\Catalog\Service\Basket\BasketFactory;
use Beeralex\Catalog\Service\CatalogSectionService;
use Beeralex\Catalog\Service\Discount\CouponsService;
use Beeralex\Catalog\Service\Discount\DiscountFactory;
use Beeralex\Catalog\Service\PriceService;
use Beeralex\Catalog\Service\SearchService;
use Beeralex\Core\Service\LanguageService;
use Beeralex\Core\Service\LocationService;
use Beeralex\Core\Service\UrlService;
use Bitrix\Main\Loader;

return [
    'services' => [
        'value' => [
            Options::class => [
                'className' => Options::class,
            ],
            DIServiceKey::PRODUCT_REPOSITORY->value => [
                'constructor' => static function () {
                    return new ProductsRepository(
                        iblockCode: 'catalog',
                        catalogService: service(\Beeralex\Core\Service\CatalogService::class),
                        catalogViewedProductRepository: service(CatalogViewedProductRepository::class)
                    );
                },
            ],
            DIServiceKey::OFFERS_REPOSITORY->value => [
                'constructor' => static function () {
                    return new OffersRepository(
                        iblockCode: 'offers',
                        catalogService: service(\Beeralex\Core\Service\CatalogService::class)
                    );
                },
            ],
            DIServiceKey::EMPTY_OFFERS_REPOSITORY->value => [
                'className' => EmptyOffersRepository::class,
            ],
            DIServiceKey::SORTING_REPOSITORY->value => [
                'constructor' => static function () {
                    return new SortingRepository(
                        iblockCode: 'sorting',
                    );
                },
            ],
            PriceTypeRepository::class => [
                'className' => PriceTypeRepository::class,
            ],
            CatalogViewedProductRepository::class => [
                'className' => CatalogViewedProductRepository::class,
            ],
            StoreRepository::class => [
                'className' => StoreRepository::class,
            ],
            PersonTypeRepository::class => [
                'className' => PersonTypeRepository::class,
            ],
            PriceRepository::class => [
                'className' => PriceRepository::class,
            ],
            CatalogSectionService::class => [
                'constructor' => static function () {
                    return new CatalogSectionService(
                        productsRepository: service(DIServiceKey::PRODUCT_REPOSITORY->value),
                        urlService: service(UrlService::class)
                    );
                }
            ],
            CatalogService::class => [
                'constructor' => static function () {
                    return new CatalogService(
                        productsRepository: service(DIServiceKey::PRODUCT_REPOSITORY->value),
                        offersRepository: service(DIServiceKey::OFFERS_REPOSITORY->value),
                        viewedProductRepository: service(CatalogViewedProductRepository::class),
                        priceTypeRepository: service(PriceTypeRepository::class),
                        sortingService: service(SortingService::class),
                        discountFactory: service(DiscountFactory::class),
                        catalogSectionService: service(CatalogSectionService::class),
                        searchService: service(SearchService::class)
                    );
                }
            ],
            OrderService::class => [
                'constructor' => static function () {
                    return new OrderService(
                        personTypeRepository: service(PersonTypeRepository::class),
                    );
                }
            ],
            PriceService::class => [
                'className' => PriceService::class,
            ],
            SearchService::class => [
                'constructor' => static function () {
                    Loader::includeModule('search');
                    return new SearchService(
                        search: new \CSearch(),
                        productRepository: service(DIServiceKey::PRODUCT_REPOSITORY->value),
                        languageService: service(LanguageService::class),
                    );
                }
            ],
            SortingService::class => [
                'constructor' => static function () {
                    return new SortingService(
                        sortingRepository: service(DIServiceKey::SORTING_REPOSITORY->value)
                    );
                }
            ],
            LocationApiClientContract::class => [
                'className' => DadataService::class,
            ],
            BitrixLocationResolverContract::class => [
                'constructor' => static function () {
                    return new BitrixLocationResolver(
                        client: service(LocationApiClientContract::class),
                        locationService: service(LocationService::class)
                    );
                }
            ],
            CouponsService::class => [
                'className' => CouponsService::class
            ],
            DiscountFactory::class => [
                'constructor' => static function () {
                    return new DiscountFactory(
                        Context::getCurrent()->getSite() ?: 's1',
                        priceService: service(PriceService::class),
                        priceRepository: service(PriceRepository::class)
                    );
                }
            ],
            BasketFactory::class => [
                'constructor' => static function () {
                    $siteId = Context::getCurrent()->getSite() ?: 's1';
                    return new BasketFactory(
                        siteId: $siteId,
                        discountFactory: service(DiscountFactory::class),
                        fuserRepository: service(FuserRepository::class)
                    );
                }
            ],
        ],
        'readonly' => true,
    ]
];
