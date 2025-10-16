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
use App\Notification\Contracts\SmsCodeContract;
use App\Notification\Contracts\SmsContract;
use App\Notification\Services\Sms\SmsAeroService;
use App\Notification\Services\Sms\SmsCodeService;
use App\User\Auth\Authenticators\EmailAuthenticator;
use App\User\Auth\Authenticators\TelegramAuthenticator;
use App\User\Auth\AuthManager;
use App\User\Auth\Contracts\EmailAuthenticatorContract;
use App\User\Auth\Contracts\ExternalAuthRepositoryContract;
use App\User\Auth\Repository\ExternalAuthRepository;
use App\User\UserRepository;
use App\User\UserRepositoryContract;
use Beeralex\Core\Helpers\IblockHelper;
use Beeralex\Core\Logger\FileLoggerFactory;
use Beeralex\Core\Logger\LoggerFactoryContract;
use Bitrix\Main\DI\ServiceLocator;

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
                    $locator = ServiceLocator::getInstance();
                    return new BitrixLocationResolver($locator->get(LocationApiClientContract::class), $locator->get(LoggerFactoryContract::class)->channel('location'));
                }
            ],
            SmsContract::class => [
                'className' => SmsAeroService::class,
            ],
            SmsCodeContract::class => [
                'constructor' => static function () {
                    $locator = ServiceLocator::getInstance();
                    return new SmsCodeService($locator->get(SmsContract::class));
                }
            ],
            CatalogSwitcherContract::class => [
                'className' => CatalogSwitcher::class
            ],
            CatalogContextContract::class => [
                'constructor' => static function () {
                    $locator = ServiceLocator::getInstance();
                    return new CatalogContext($locator->get(CatalogSwitcherContract::class), SectionModel::compileEntityByIblock(IblockHelper::getIblockIdByCode('catalog')));
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
            UserRepositoryContract::class => [
                'className' => UserRepository::class,
            ],
            ExternalAuthRepositoryContract::class => [
                'className' => ExternalAuthRepository::class,
            ],
            AuthManager::class => [
                'constructor' => static function () {
                    $locator = ServiceLocator::getInstance();
                    $userRepository = $locator->get(UserRepositoryContract::class);
                    $externalAuthRepository = $locator->get(ExternalAuthRepositoryContract::class);
                    return new AuthManager([
                        EmailAuthenticator::getKey() => new EmailAuthenticator($userRepository),
                        TelegramAuthenticator::getKey() => new TelegramAuthenticator($userRepository, $externalAuthRepository),
                    ]);
                }
            ]
        ],
        'readonly' => true,
    ]
];
