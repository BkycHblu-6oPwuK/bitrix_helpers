<?php

namespace App\User\Auth\Authenticators;

use App\User\Auth\Contracts\AuthenticatorContract;
use App\User\Auth\Contracts\ExternalAuthRepositoryContract;
use App\User\Dto\BaseUserDto;
use App\User\Exceptions\RegistrationException;
use App\User\UserBuilder;
use App\User\UserRepositoryContract;

abstract class BaseServiceAuthentificator extends BaseAuthentificator
{
    public function __construct(
        protected readonly UserRepositoryContract $userRepository,
        protected readonly ExternalAuthRepositoryContract $externalAuthRepository
    ) {}

    public function authenticate(BaseUserDto $data): void
    {
        $authKey = static::getKey();
        $link = $this->externalAuthRepository->getByExternalId($data->externalId, $authKey);
        if ($link) {
            $this->authorizeByUserId($link['USER_ID']);
            return;
        }
        $userBuilder = new UserBuilder();
        $userBuilder->setExternalId($data->externalId)
            ->setAuthentificatorKey($authKey)
            ->setName($data->name ?? '')
            ->setLastName($data->lastName ?? '');
        $userId = $this->userRepository->add($userBuilder->build());

        $this->externalAuthRepository->saveLink($userId, $authKey, $data->externalId);
        $this->authorizeByUserId($userId);
    }


    public function isService(): bool
    {
        return true;
    }
}
