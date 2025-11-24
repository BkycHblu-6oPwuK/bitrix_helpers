<?php
require_once __DIR__ . '/lib/Enum/DIServiceKey.php';

use Beeralex\Catalog\Basket\BasketFacade;
use Beeralex\Catalog\Basket\BasketUtils;
use Beeralex\Catalog\Discount\Coupons;
use Beeralex\Catalog\Discount\Discount;
use Beeralex\Catalog\Enum\DIServiceKey;
use Beeralex\Catalog\Location\BitrixLocationResolver;
use Beeralex\Catalog\Location\Contracts\BitrixLocationResolverContract;
use Beeralex\Catalog\Location\Contracts\LocationApiClientContract;
use Beeralex\Catalog\Location\Services\DadataService;
use Beeralex\Catalog\Repository\CatalogViewedProductRepository;
use Beeralex\Catalog\Repository\EmptyOffersRepository;
use Beeralex\Catalog\Repository\OffersRepository;
use Beeralex\Catalog\Repository\PriceTypeRepository;
use Beeralex\Catalog\Repository\ProductsRepository;
use Beeralex\Catalog\Service\CatalogService;
use Beeralex\Core\Logger\LoggerFactoryContract;
use Bitrix\Main\Context;
use Bitrix\Sale\Basket;
use Bitrix\Sale\BasketBase;
use Bitrix\Sale\Fuser;
use Beeralex\Catalog\Options;
use Beeralex\Catalog\Service\PriceService;

return [
    'services' => [
        'value' => [
            Options::class => [
                'className' => Options::class,
            ],
            LocationApiClientContract::class => [
                'className' => DadataService::class,
            ],
            BitrixLocationResolverContract::class => [
                'constructor' => static function () {
                    return new BitrixLocationResolver(service(LocationApiClientContract::class), service(LoggerFactoryContract::class)->channel('location'));
                }
            ],
            DIServiceKey::CATALOG_REPOSITORY->value => [
                'constructor' => static function () {
                    $options = service(Options::class);
                    return new ProductsRepository(
                        'catalog',
                        $options,
                        service(\Beeralex\Core\Service\CatalogService::class)
                    );
                },
            ],
            DIServiceKey::OFFERS_REPOSITORY->value => [
                'constructor' => static function () {
                    $options = service(Options::class);
                    return new OffersRepository(
                        'offers',
                        $options,
                        service(\Beeralex\Core\Service\CatalogService::class)
                    );
                },
            ],
            DIServiceKey::EMPTY_OFFERS_REPOSITORY->value => [
                'className' => EmptyOffersRepository::class,
            ],
            PriceTypeRepository::class => [
                'className' => PriceTypeRepository::class,
            ],
            CatalogViewedProductRepository::class => [
                'className' => CatalogViewedProductRepository::class,
            ],
            CatalogService::class => [
                'constructor' => static function () {
                    return new CatalogService(
                        service(DIServiceKey::CATALOG_REPOSITORY->value),
                        service(DIServiceKey::OFFERS_REPOSITORY->value),
                        service(CatalogViewedProductRepository::class)
                    );
                }
            ],
            PriceService::class => [
                'className' => PriceService::class,
            ],
            /** возможно стоит создать фабрику, а не создавать объекты для текущего пользователя из сессии */
            BasketUtils::class => [
                'constructor' => static function () {
                    return new BasketUtils(
                        service(DIServiceKey::CATALOG_REPOSITORY->value),
                        service(DIServiceKey::OFFERS_REPOSITORY->value),
                        service(BasketBase::class)
                    );
                }
            ],
            Coupons::class => [
                'className' => Coupons::class
            ],
            Discount::class => [
                'constructor' => static function () {
                    return new Discount(service(BasketBase::class));
                }
            ],
            BasketBase::class => [ // корзина текущего юзера
                'constructor' => static function () {
                    return Basket::loadItemsForFUser(Fuser::getId(), Context::getCurrent()->getSite());
                }
            ],
            BasketFacade::class => [
                'constructor' => static function () {
                    return new BasketFacade(
                        service(BasketBase::class),
                        service(BasketUtils::class),
                        service(Coupons::class),
                        service(Discount::class)
                    );
                }
            ]
        ],
        'readonly' => true,
    ]
];
