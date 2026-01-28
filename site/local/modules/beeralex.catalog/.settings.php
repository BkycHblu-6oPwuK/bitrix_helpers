<?php
require_once __DIR__ . '/lib/Enum/DIServiceKey.php';

use Beeralex\Catalog\Contracts\StoreRepositoryContract;
use Beeralex\Catalog\Enum\DIServiceKey;
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
use Beeralex\Catalog\Service\CatalogElementService;
use Beeralex\Catalog\Service\CatalogSectionsService;
use Beeralex\Catalog\Service\Discount\CouponsService;
use Beeralex\Catalog\Service\Discount\DiscountFactory;
use Beeralex\Catalog\Service\OrderService;
use Beeralex\Catalog\Service\PriceService;
use Beeralex\Catalog\Service\SearchService;
use Beeralex\Core\Repository\PropertyFeaturesRepository;
use Beeralex\Core\Repository\PropertyRepository;
use Beeralex\Core\Service\FileService;
use Beeralex\Core\Service\HlblockService;
use Beeralex\Core\Service\LanguageService;
use Beeralex\Core\Service\LocationService;
use Beeralex\Core\Service\SortingService;
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
                        catalogViewedProductRepository: service(CatalogViewedProductRepository::class),
                        urlService: service(UrlService::class)
                    );
                },
            ],
            DIServiceKey::OFFERS_REPOSITORY->value => [
                'constructor' => static function () {
                    return new OffersRepository(
                        iblockCode: 'offers',
                        catalogService: service(\Beeralex\Core\Service\CatalogService::class),
                        urlService: service(UrlService::class)
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
            StoreRepositoryContract::class => [
                'className' => StoreRepositoryContract::class,
            ],
            PersonTypeRepository::class => [
                'className' => PersonTypeRepository::class,
            ],
            PriceRepository::class => [
                'className' => PriceRepository::class,
            ],
            CatalogSectionsService::class => [
                'constructor' => static function () {
                    return new CatalogSectionsService(
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
                        sortingService: service(DIServiceKey::SORTING_SERVICE->value),
                        discountFactory: service(DiscountFactory::class),
                        catalogSectionsService: service(CatalogSectionsService::class),
                        searchService: service(SearchService::class)
                    );
                }
            ],
            CatalogElementService::class => [
                'constructor' => static function () {
                    return new CatalogElementService(
                        productRepository: service(DIServiceKey::PRODUCT_REPOSITORY->value),
                        offersRepository: service(DIServiceKey::OFFERS_REPOSITORY->value),
                        propertyRepository: service(PropertyRepository::class),
                        propertyFeaturesRepository: service(PropertyFeaturesRepository::class),
                        hlblockService: service(HlblockService::class),
                        fileService: service(FileService::class),
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
                    $productsRepository = service(DIServiceKey::PRODUCT_REPOSITORY->value);
                    $iblockType = $productsRepository->query()->setSelect(['IBLOCK_TYPE_ID' => 'IBLOCK.IBLOCK_TYPE_ID'])->cacheJoins(true)->setCacheTtl(864000)->fetch()['IBLOCK_TYPE_ID'];
                    return new SearchService(
                        factoryIncludeComponent: fn() => $GLOBALS['APPLICATION']->IncludeComponent(
                            "bitrix:search.page",
                            "",
                            array(
                                "RESTART" => 'Y',
                                "NO_WORD_LOGIC" => 'Y',
                                "USE_LANGUAGE_GUESS" => 'N',
                                "CHECK_DATES" => 'Y',
                                "arrFILTER" => array("iblock_{$iblockType}"),
                                "arrFILTER_iblock_{$iblockType}" => array($productsRepository->entityId),
                                "USE_TITLE_RANK" => "N",
                                "DEFAULT_SORT" => "rank",
                                "FILTER_NAME" => "",
                                "SHOW_WHERE" => "N",
                                "arrWHERE" => array(),
                                "SHOW_WHEN" => "N",
                                "PAGE_RESULT_COUNT" => 10000,
                                "DISPLAY_TOP_PAGER" => "N",
                                "DISPLAY_BOTTOM_PAGER" => "N",
                                "PAGER_TITLE" => "",
                                "PAGER_SHOW_ALWAYS" => "N",
                                "PAGER_TEMPLATE" => "N",
                            ),
                        ),
                        productRepository: service(DIServiceKey::PRODUCT_REPOSITORY->value),
                        languageService: service(LanguageService::class),
                    );
                }
            ],
            DIServiceKey::SORTING_SERVICE->value => [
                'constructor' => static function () {
                    return new SortingService(
                        sortingRepository: service(DIServiceKey::SORTING_REPOSITORY->value)
                    );
                }
            ],
            LocationApiClientContract::class => [
                'constructor' => static function () {
                    $options = \service(Options::class);
                    return new DadataService(
                        apiKey: $options->apiKey,
                        secretKey: $options->secretKey
                    );
                },
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
