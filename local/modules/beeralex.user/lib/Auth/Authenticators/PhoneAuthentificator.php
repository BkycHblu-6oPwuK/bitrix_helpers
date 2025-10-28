<?php

namespace Beeralex\User\Auth\Authenticators;

use Beeralex\User\Auth\Contracts\PhoneAuthentificatorContract;
use Beeralex\User\Dto\BaseUserDto;
use Beeralex\User\Phone;

class PhoneAuthentificator extends BaseAuthentificator implements PhoneAuthentificatorContract
{
    public function getKey(): string
    {
        return 'phone';
    }

    public function getTitle(): string
    {
        return 'Авторизация по номеру телефона';
    }

    public function authenticate(?BaseUserDto $data = null): void
    {
        if ($data === null) {
            throw new \InvalidArgumentException("Phone number must be provided for phone authentication");
        }
        $this->authenticateByPhone(
            Phone::fromString($data->phone),
        );
    }

    public function authenticateByPhone(Phone $phone): void
    {
        $user = $this->userRepository->getByPhone($phone);

        // $this->authorizeByUserId($user->getId());
    }
}
