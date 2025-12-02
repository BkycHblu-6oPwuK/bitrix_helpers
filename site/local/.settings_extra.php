<?php

use App\Repository\OffersRepository;
use App\Repository\ProductsRepository;
use Beeralex\Catalog\Enum\DIServiceKey;
use Beeralex\Catalog\Repository\CatalogViewedProductRepository;
use Beeralex\Core\Service\CatalogService;
use Beeralex\Core\Service\UrlService;
use Bitrix\Main\Loader;

require_once __DIR__ . '/modules/beeralex.catalog/lib/Enum/DIServiceKey.php';
/**
 * зарегистрированные зависимости в этом файле перебивают зарегистрированные зависимости в модулях
 * Здесь сервисы из модулей можно переопределить на свои реализации
 * Это нужно для того, чтобы не править код модулей напрямую
 * В модуле остается основа, а здесь реализация конкретного проекта
 */
return [
    'composer' => [
        'value' => ['config_path' => 'composer.json']
    ],
    'routing' => ['value' => [
        'config' => ['web.php', 'api.php']
    ]],
    'beeralex.oauth2' => [
        'value' => [
            'private_key' => '/var/www/local/config/private.key',
            'public_key' => '/var/www/local/config/public.key',
            'private_key_passphrase' => null,
            'encryption_key' => 'KMC0N/+dFrGoB2CYlH3q2XQwLJBLvY2En6+fS4i9rZs=', // ключ шифрования
        ]
    ],
    'services' => [
        'value' => [
            DIServiceKey::PRODUCT_REPOSITORY->value => [
                'constructor' => static function () {
                    Loader::requireModule('beeralex.catalog');
                    return new ProductsRepository('catalog', service(CatalogService::class), service(CatalogViewedProductRepository::class), service(UrlService::class));
                }
            ],
            DIServiceKey::OFFERS_REPOSITORY->value => [
                'constructor' => static function () {
                    Loader::requireModule('beeralex.catalog');
                    return new OffersRepository('offers', service(CatalogService::class), service(UrlService::class));
                }
            ],
        ]
    ]
    // 'beeralex.api' => [
    //     'value' => [
    //         'remove_parts' => [
    //             '/api/',
    //             '/v1/',
    //         ]
    //     ]
    // ],
];
