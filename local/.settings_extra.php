<?php
require_once $_SERVER['DOCUMENT_ROOT'] . '/local/lib/App/Main/Enum/DIServiceKey.php'; // автоподгрузчик еще не подключен в момент выполнения файла

use App\Catalog\Location\BitrixLocationResolver;
use App\Catalog\Location\Contracts\BitrixLocationResolverContract;
use App\Catalog\Location\Contracts\LocationApiClientContract;
use App\Catalog\Location\Services\DadataService;
use App\Catalog\Repository\EmptyOffersRepository;
use App\Catalog\Repository\OffersRepository;
use App\Catalog\Repository\ProductsRepository;
use App\Catalog\Type\CatalogContext;
use App\Catalog\Type\CatalogSwitcher;
use App\Catalog\Type\Contracts\CatalogContextContract;
use App\Catalog\Type\Contracts\CatalogSwitcherContract;
use App\Iblock\Model\SectionModel;
use App\Main\Enum\DIServiceKey;
use Beeralex\Core\Helpers\IblockHelper;
use Beeralex\Core\Logger\FileLoggerFactory;
use Beeralex\Core\Logger\LoggerFactoryContract;

/**
 * зарегистрированные зависимости в этом файле перебивают зарегистрированные зависимости в модулях
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
            LoggerFactoryContract::class => [
                'constructor' => static function () {
                    return new FileLoggerFactory($_SERVER['DOCUMENT_ROOT'] . '/local');
                },
            ],
            LocationApiClientContract::class => [
                'className' => DadataService::class,
            ],
            BitrixLocationResolverContract::class => [
                'constructor' => static function () {
                    return new BitrixLocationResolver(service(LocationApiClientContract::class), service(LoggerFactoryContract::class)->channel('location'));
                }
            ],
            CatalogSwitcherContract::class => [
                'className' => CatalogSwitcher::class
            ],
            CatalogContextContract::class => [
                'constructor' => static function () {
                    return new CatalogContext(service(CatalogSwitcherContract::class), SectionModel::compileEntityByIblock(IblockHelper::getIblockIdByCode('catalog')));
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
        ],
        'readonly' => true,
    ]
];
