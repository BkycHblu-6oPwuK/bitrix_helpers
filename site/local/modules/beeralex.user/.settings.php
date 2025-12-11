<?php

use Beeralex\User\Auth\Authenticators\EmailAuthenticator;
use Beeralex\User\Auth\Authenticators\EmptyAuthentificator;
use Beeralex\User\Auth\Authenticators\PhoneAuthentificator;
use Beeralex\User\Auth\Authenticators\SocialServiceAuthenticatorFactory;
use Beeralex\User\Auth\AuthManager;
use Beeralex\User\Auth\Contracts\EmailAuthenticatorContract;
use Beeralex\User\Auth\Contracts\PhoneAuthentificatorContract;
use Beeralex\User\Auth\Social\SocialAdaptersFactory;
use Beeralex\User\Auth\Social\SocialManager;
use Beeralex\User\Auth\SocialAuthenticatorFactory;
use Beeralex\User\Contracts\UserBuilderContract;
use Beeralex\User\Contracts\UserFactoryContract;
use Beeralex\User\Auth\Contracts\UserPhoneAuthRepositoryContract;
use Beeralex\User\Auth\JwtTokenManager;
use Beeralex\User\Contracts\UserRepositoryContract;
use Beeralex\User\Auth\UserPhoneAuthRepository;
use Beeralex\User\UserBuilder;
use Beeralex\User\UserFactory;
use Beeralex\User\UserRepository;
use Beeralex\User\Auth\PhoneCodeService;
use Beeralex\User\Auth\Validator\AuthEmailValidator;
use Beeralex\User\Auth\Validator\AuthPhoneValidator;
use Beeralex\User\Options;
use Beeralex\User\Auth\AuthService;

return [
    'controllers' => [
        'value' => [
            'defaultNamespace' => '\\Beeralex\\User\\Controllers',
        ],
        'readonly' => true,
    ],
    'services' => [
        'value' => [
            UserRepositoryContract::class => [
                'constructor' => static function () {
                    return new UserRepository(service(UserFactoryContract::class), service(\Beeralex\Core\Service\FileService::class));
                }
            ],
            UserFactoryContract::class => [
                'className' => UserFactory::class,
            ],
            UserBuilderContract::class => [
                'constructor' => static function () {
                    return new UserBuilder(service(UserFactoryContract::class), service(\Beeralex\Core\Service\UserService::class));
                }
            ],
            EmptyAuthentificator::class => [
                'className' => EmptyAuthentificator::class,
            ],
            EmailAuthenticatorContract::class => [
                'constructor' => static function () {
                    return new EmailAuthenticator(service(UserRepositoryContract::class));
                }
            ],
            UserPhoneAuthRepositoryContract::class => [
                'className' => UserPhoneAuthRepository::class
            ],
            PhoneCodeService::class => [
                'constructor' => static function () {
                    return new PhoneCodeService(service(UserPhoneAuthRepositoryContract::class));
                }
            ],
            PhoneAuthentificatorContract::class => [
                'constructor' => static function () {
                    return new PhoneAuthentificator(service(PhoneCodeService::class), service(UserRepositoryContract::class));
                }
            ],
            JwtTokenManager::class => [
                'constructor' => static function () {
                    return new JwtTokenManager(service(Options::class));
                }
            ],
            SocialAuthenticatorFactory::class => [
                'constructor' => static function () {
                    return new SocialAuthenticatorFactory(service(SocialManager::class), service(SocialServiceAuthenticatorFactory::class));
                }
            ],
            SocialServiceAuthenticatorFactory::class => [
                'className' => SocialServiceAuthenticatorFactory::class,
            ],
            AuthEmailValidator::class => [
                'className' => AuthEmailValidator::class
            ],
            AuthPhoneValidator::class => [
                'className' => AuthPhoneValidator::class
            ],
            AuthManager::class => [
                'constructor' => static function () {
                    $emailAuth = service(EmailAuthenticatorContract::class);
                    $phoneAuth = service(PhoneAuthentificatorContract::class);
                    $socialFactory = service(SocialAuthenticatorFactory::class);
                    $socialAuthenticators = $socialFactory->makeAll();
                    return new AuthManager(array_merge([
                        $emailAuth->getKey() => $emailAuth,
                        $phoneAuth->getKey() => $phoneAuth,
                    ], $socialAuthenticators), [
                        $emailAuth->getKey() => service(AuthEmailValidator::class),
                        $phoneAuth->getKey() => service(AuthPhoneValidator::class)
                    ]);
                }
            ],
            SocialManager::class => [
                'constructor' => static function () {
                    $socialFactory = new SocialAdaptersFactory();
                    $adapters = $socialFactory->makeAll();
                    return new SocialManager($adapters);
                }
            ],
            AuthService::class => [
                'constructor' => static function () {
                    return new AuthService(
                        service(AuthManager::class),
                        service(JwtTokenManager::class)
                    );
                }
            ],
        ],
    ]
];
