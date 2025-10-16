<?php

namespace App\User\Auth\Authenticators;

use App\User\Auth\Contracts\ExternalAuthRepositoryContract;
use App\User\Dto\BaseUserDto;
use App\User\Phone\Phone;
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

        $link = $this->externalAuthRepository->getByExternalId($data->id, $authKey);

        if ($link) {
            $this->authorizeByUserId($link['USER_ID']);
            return;
        }

        $existingUser = null;
        $phone = null;
        if($data->phone) {
            $phone = new Phone($data->phone);
            $existingUser = $this->userRepository->getByPhone($phone);
        } elseif ($data->email) {
            $existingUser = $this->userRepository->getByEmail($data->email);
        }

        if ($existingUser) {
            $this->externalAuthRepository->saveLink($existingUser->getId(), $authKey, $data->id);
            $this->authorizeByUserId($existingUser->getId());
            return;
        }

        $userBuilder = (new UserBuilder())
            ->setName($data->first_name ?? $data->name ?? '')
            ->setLastName($data->last_name ?? '')
            ->setExternalId($data->id)
            ->setAuthentificatorKey($authKey);

        if (!$data->email) {
            $userBuilder->setEmail($data->email);
        }
        if($phone) {
            $userBuilder->setPhone($phone);
        }

        $userId = $this->userRepository->add($userBuilder->build());

        $this->externalAuthRepository->saveLink($userId, $authKey, $data->id);

        $this->authorizeByUserId($userId);
    }

    public function isService(): bool
    {
        return true;
    }
}
