<?php

use Beeralex\User\Auth\Authenticators\EmailAuthenticator;
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

return [
    'services' => [
        'value' => [
            UserRepositoryContract::class => [
                'constructor' => static function () {
                    return new UserRepository(service(UserFactoryContract::class));
                }
            ],
            UserFactoryContract::class => [
                'className' => UserFactory::class,
            ],
            UserBuilderContract::class => [
                'constructor' => static function () {
                    return new UserBuilder(service(UserFactoryContract::class));
                }
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
                'className' => JwtTokenManager::class,
            ],
            SocialAuthenticatorFactory::class => [
                'constructor' => static function () {
                    return new SocialAuthenticatorFactory(service(SocialManager::class), service(SocialServiceAuthenticatorFactory::class));
                }
            ],
            SocialServiceAuthenticatorFactory::class => [
                'className' => SocialServiceAuthenticatorFactory::class,
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
                    ], $socialAuthenticators), service(JwtTokenManager::class));
                }
            ],
            SocialManager::class => [
                'constructor' => static function () {
                    $socialFactory = new SocialAdaptersFactory();
                    $adapters = $socialFactory->makeAll();
                    return new SocialManager($adapters);
                }
            ],
        ],
    ]
];
