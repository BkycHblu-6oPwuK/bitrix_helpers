<?php

namespace Beeralex\User\Auth\Authenticators;

use Beeralex\User\Auth\Contracts\EmailAuthenticatorContract;
use Beeralex\User\Dto\BaseUserDto;
use Beeralex\User\Exceptions\IncorrectOldPasswordException;
use Beeralex\User\Exceptions\UserNotFoundException;
use Beeralex\User\PasswordValidator;
use Beeralex\User\User;
use Bitrix\Main\Security\Password;

class EmailAuthenticator extends BaseAuthentificator implements EmailAuthenticatorContract
{
    public function getKey(): string
    {
        return 'email';
    }

    public function getTitle(): string
    {
        return 'Авторизация по E-mail';
    }

    public function authenticate(?BaseUserDto $data = null): void
    {
        if ($data === null) {
            throw new \InvalidArgumentException("data must be provided for email authentication");
        }
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

        if (Password::equals($user->getPassword(), $password)) {
            throw new IncorrectOldPasswordException();
        }

        $this->authorizeByUserId($user->getId());
    }
}
