<?php
declare(strict_types=1);
namespace Beeralex\User\Auth\Authenticators;

use Beeralex\User\Auth\Contracts\AuthenticatorContract;
use Beeralex\User\Contracts\UserRepositoryContract;
use Beeralex\User\Auth\AuthCredentialsDto;
use Beeralex\User\UserBuilder;
use Bitrix\Main\Result;

abstract class AbstractAuthentificator implements AuthenticatorContract
{
    public function __construct(
        protected readonly UserRepositoryContract $userRepository,
    ) {}

    public function authorizeByUserId(int $userId): void
    {
        (new \CUser())->Authorize($userId);
    }

    public function register(AuthCredentialsDto $credentials): Result
    {
        $result = new Result();
        try {
            $this->userRepository->add(UserBuilder::fromDto($credentials)->build());
        } catch (\Exception $e) {
            $result->addError(new \Bitrix\Main\Error($e->getMessage()));
        }
        return $result;
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
        return false;
    }

    public function getAuthorizationUrlOrHtml(): ?array
    {
        return null;
    }
}
