<?php

namespace App\User\Auth\Authenticators;

use App\User\Auth\Contracts\PhoneAuthenticatorContract;
use App\User\Dto\BaseUserDto;
use App\User\Exceptions\IncorrectOldPasswordException;
use App\User\Exceptions\UserNotFoundException;
use App\User\PasswordValidator;
use App\User\Phone\Phone;

class EmailAuthenticator extends BaseAuthentificator implements PhoneAuthenticatorContract
{
    public static function getKey(): string
    {
        return 'phone';
    }

    public function getTitle(): string
    {
        return 'Авторизация по номеру телефона';
    }

    public function authenticate(BaseUserDto $data): void
    {
        
    }

    public function authenticateByPhone(Phone $phone): void
    {
        // $user = $this->userRepository->getByPhone($email);

        // if (!$user) {
        //     throw new UserNotFoundException("User with email {$email} not found");
        // }

        // if (!(new PasswordValidator())->validatePassword($password, $user->getPassword())) {
        //     throw new IncorrectOldPasswordException();
        // }

        // $this->authorizeByUserId($user->getId());
    }
}
