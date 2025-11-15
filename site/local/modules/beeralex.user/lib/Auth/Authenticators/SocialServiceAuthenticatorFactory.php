<?php
declare(strict_types=1);
namespace Beeralex\User\Auth\Authenticators;

use Beeralex\User\Auth\Contracts\AuthenticatorContract;
use Beeralex\User\Auth\Social\Contracts\SocialServiceProviderContract;

/**
 * Фабрика для создания аутентификаторов социальных сервисов
 */
class SocialServiceAuthenticatorFactory
{
    public function create(SocialServiceProviderContract $provider): AuthenticatorContract
    {
        return new SocialServiceAuthenticator($provider);
    }
}
