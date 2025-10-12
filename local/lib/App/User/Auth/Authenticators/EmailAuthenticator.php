<?php

namespace App\User\Auth\Authenticators;

use App\User\Auth\Contracts\EmailAuthenticatorContract;
use App\User\Exceptions\IncorrectOldPasswordException;
use App\User\Exceptions\UserNotFoundException;
use App\User\PasswordValidator;
use App\User\UserRepository;

class EmailAuthenticator extends BaseAuthentificator implements EmailAuthenticatorContract
{
    public static function getKey(): string
    {
        return 'email';
    }

    public function authenticate(array $credentials): void
    {
        $this->authenticateByEmail(
            $credentials['email'] ?? '',
            $credentials['password'] ?? ''
        );
    }

    public function authenticateByEmail(string $email, string $password): void
    {
        $user = (new UserRepository())->getByEmail($email);
        if (!isset($user)) {
            throw new UserNotFoundException("User with email {$email} not found");
        }
        // проверяем есть ли пользователь с данным емаилом и указанным паролем
        if (!(new PasswordValidator())->validatePassword($password, $user->getPassword())) {
            throw new IncorrectOldPasswordException();
        }

        // авторизуем пользователя
        $this->authorizeByUserId($user->getId());
    }
}
