<?php

namespace App\User\Auth\Authenticators;

use App\User\Auth\Contracts\EmailAuthenticatorContract;
use App\User\Dto\BaseUserDto;
use App\User\Exceptions\IncorrectOldPasswordException;
use App\User\Exceptions\UserNotFoundException;
use App\User\PasswordValidator;
use App\User\User;

class EmailAuthenticator extends BaseAuthentificator implements EmailAuthenticatorContract
{
    public static function getKey(): string
    {
        return 'email';
    }

    public function getTitle(): string
    {
        return 'Авторизация по E-mail';
    }

    public function authenticate(BaseUserDto $data): void
    {
        $this->authenticateByEmail(
            $data->email,
            $data->password
        );
    }

    public function authenticateByEmail(string $email, string $password): void
    {
        $user = $this->userRepository->getByEmail($email);

        if (!$user) {
            throw new UserNotFoundException("User with email {$email} not found");
        }

        if (!(new PasswordValidator())->validatePassword($password, $user->getPassword())) {
            throw new IncorrectOldPasswordException();
        }

        $this->authorizeByUserId($user->getId());
    }
}
