<?php

namespace App\User\Services;

use App\User\Exceptions\ChangePasswordException;
use App\User\Exceptions\InvalidPasswordException;
use App\User\Exceptions\UserNotFoundException;
use App\User\UserRepository;
use App\User\User;
use App\User\UserValidator;

class UserService
{
    /**
     * Меняет пароль у пользователя
     * Можно вызвать только для зарегистрированного пользователя с указанным ID
     *
     * @param User   $user
     * @param string $password
     *
     * @throws InvalidPasswordException
     * @throws ChangePasswordException
     */
    public function changePassword(User $user, string $password): void
    {
        $validator = new UserValidator();
        if (!$validator->validatePassword($password)) {
            throw new InvalidPasswordException(join(', ', $validator->getErrors()));
        }

        try {
            (new UserRepository())->update($user->getId(), [
                'PASSWORD' => $password
            ]);
        } catch (\Exception $e) {
            throw new ChangePasswordException($e->getMessage(), 0, $e);
        }
    }

    /**
     * Восстановление пароля пользователя
     *
     * @param string $email
     */
    public function restorePassword(string $email): void
    {
        $user = (new UserRepository())->getByEmail($email);
        if (!$user) {
            throw new UserNotFoundException($email);
        }

        \CUser::SendUserInfo($user->getId(), SITE_ID, '', false, 'USER_PASS_REQUEST');
    }
}
