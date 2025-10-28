<?php

namespace Beeralex\User\Auth\Authenticators;

use Beeralex\User\Auth\Contracts\AuthenticatorContract;
use Beeralex\User\Auth\Social\Contracts\SocialServiceProviderContract;
use Beeralex\User\Dto\BaseUserDto;

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

    public function register(BaseUserDto $user): void
    {
        throw new \LogicException("Registration handled internally by Bitrix socialservices");
    }

    /**
     * Выполняем авторизацию пользователя через Bitrix socialservice.
     * Bitrix сам создаст пользователя и залогинит его.
     */
    public function authenticate(?BaseUserDto $data = null): void
    {
        if (!$this->provider->authorize()) {
            throw new \RuntimeException("Authorization failed via {$this->provider->getKey()}");
        }

        // После успешной авторизации Bitrix уже выполнил login.
        $profile = $this->provider->getProfile();

        // if ($profile) {
        //     $data->id = $profile['id'] ?? null;
        //     $data->email = $profile['email'] ?? null;
        //     $data->first_name = $profile['first_name'] ?? '';
        //     $data->last_name = $profile['last_name'] ?? '';
        // }
    }

    public function getAuthorizationUrl(): string
    {
        return $this->provider->getAuthorizationUrl();
    }
}
