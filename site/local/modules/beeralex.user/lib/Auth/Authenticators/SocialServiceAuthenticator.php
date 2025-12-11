<?php
declare(strict_types=1);
namespace Beeralex\User\Auth\Authenticators;

use Beeralex\User\Auth\Contracts\AuthenticatorContract;
use Beeralex\User\Auth\Social\Contracts\SocialServiceProviderContract;
use Beeralex\User\Auth\AuthCredentialsDto;
use Bitrix\Main\Result;

class SocialServiceAuthenticator implements AuthenticatorContract
{
    public function __construct(
        protected readonly SocialServiceProviderContract $provider,
    ) {}

    public function getKey(): string
    {
        return $this->provider->getKey();
    }

    public function getTitle(): string
    {
        return $this->provider->getName();
    }

    public function getDescription(): ?string
    {
        return null;
    }

    public function getLogoUrl(): ?string
    {
        return null;
    }

    public function isService(): bool
    {
        return true;
    }

    public function register(AuthCredentialsDto $credentials): Result
    {
        $result = new Result();
        $result->addError(new \Bitrix\Main\Error('Registration via social service authenticator is not supported'));
        return $result;
    }

    /**
     * Выполняем авторизацию пользователя через Bitrix socialservice.
     * Bitrix сам создаст пользователя и залогинит его.
     */
    public function authenticate(AuthCredentialsDto $credentials): Result
    {
        $result = new Result();
        if (!$this->provider->authorize()) {
            $result->addError(new \Bitrix\Main\Error("Authorization failed via {$this->provider->getKey()}"));
            return $result;
        }

        return $result;
    }

    public function getAuthorizationUrlOrHtml(): ?array
    {
        return $this->provider->getAuthorizationUrlOrHtml();
    }
}
