<?php

namespace App\User\Auth\Authenticators;

use App\User\Auth\Contracts\AuthenticatorContract;
use App\User\Auth\Contracts\ExternalAuthRepositoryContract;
use App\User\Dto\BaseUserDto;
use App\User\Exceptions\RegistrationException;
use App\User\UserBuilder;
use App\User\UserRepositoryContract;

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
}
