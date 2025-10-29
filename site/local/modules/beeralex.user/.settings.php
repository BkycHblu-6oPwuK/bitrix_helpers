<?php

use Beeralex\User\Auth\Authenticators\EmailAuthenticator;
use Beeralex\User\Auth\Authenticators\PhoneAuthentificator;
use Beeralex\User\Auth\AuthManager;
use Beeralex\User\Auth\Contracts\EmailAuthenticatorContract;
use Beeralex\User\Auth\Contracts\PhoneAuthentificatorContract;
use Beeralex\User\Auth\Social\SocialAdaptersFactory;
use Beeralex\User\Auth\Social\SocialManager;
use Beeralex\User\Auth\SocialAuthenticatorFactory;
use Beeralex\User\Contracts\UserBuilderContract;
use Beeralex\User\Contracts\UserFactoryContract;
use Beeralex\User\Contracts\UserRepositoryContract;
use Beeralex\User\UserBuilder;
use Beeralex\User\UserFactory;
use Beeralex\User\Repository\UserRepository;

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
            PhoneAuthentificatorContract::class => [
                'constructor' => static function () {
                    return new PhoneAuthentificator(service(UserRepositoryContract::class));
                }
            ],
            AuthManager::class => [
                'constructor' => static function () {
                    $emailAuth = service(EmailAuthenticatorContract::class);
                    $phoneAuth = service(PhoneAuthentificatorContract::class);
                    $socialFactory = new SocialAuthenticatorFactory();
                    $socialAuthenticators = $socialFactory->makeAll();
                    return new AuthManager(array_merge([
                        $emailAuth->getKey() => $emailAuth,
                        $phoneAuth->getKey() => $phoneAuth,
                    ], $socialAuthenticators));
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
