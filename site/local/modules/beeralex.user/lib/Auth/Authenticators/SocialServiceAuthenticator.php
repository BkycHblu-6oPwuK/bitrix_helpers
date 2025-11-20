<?php
declare(strict_types=1);
namespace Beeralex\User\Auth\Authenticators;

use Beeralex\User\Auth\Contracts\AuthenticatorContract;
use Beeralex\User\Auth\Social\Contracts\SocialServiceProviderContract;
use Beeralex\User\Dto\AuthCredentialsDto;
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

    public function register(AuthCredentialsDto $user): Result
    {
        throw new \LogicException("Registration handled internally by Bitrix socialservices");
    }

    /**
     * Выполняем авторизацию пользователя через Bitrix socialservice.
     * Bitrix сам создаст пользователя и залогинит его.
     */
    public function authenticate(?AuthCredentialsDto $data = null): Result
    {
        $result = new Result();
        if (!$this->provider->authorize()) {
            $result->addError(new \Bitrix\Main\Error("Authorization failed via {$this->provider->getKey()}"));
            return $result;
        }

        // После успешной авторизации Bitrix уже выполнил login.
        $profile = $this->provider->getProfile();

        // if ($profile) {
        //     $data->id = $profile['id'] ?? null;
        //     $data->email = $profile['email'] ?? null;
        //     $data->first_name = $profile['first_name'] ?? '';
        //     $data->last_name = $profile['last_name'] ?? '';
        // }
        return $result;
    }

    public function getAuthorizationUrlOrHtml(): ?array
    {
        return $this->provider->getAuthorizationUrlOrHtml();
    }
}
