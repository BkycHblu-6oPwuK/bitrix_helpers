<?php

namespace Beeralex\User\Auth\Authenticators;

use Beeralex\User\Auth\Contracts\AuthenticatorContract;
use Beeralex\User\Contracts\UserRepositoryContract;
use Beeralex\User\Dto\BaseUserDto;
use Beeralex\User\Exceptions\RegistrationException;
use Beeralex\User\UserBuilder;

abstract class BaseAuthentificator implements AuthenticatorContract
{
    public function __construct(
        protected readonly UserRepositoryContract $userRepository,
    ) {}

    public function authorizeByUserId(int $userId): void
    {
        (new \CUser())->Authorize($userId);
    }

    public function register(BaseUserDto $userDto): void
    {
        try {
            $id = $this->userRepository->add(UserBuilder::fromDto($userDto)->build());
        } catch (\Exception $e) {
            throw new RegistrationException($e->getMessage(), 0, $e);
        }

        $this->authorizeByUserId($id);
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

    public function getAuthorizationUrl(): string
    {
        return '';
    }
}
