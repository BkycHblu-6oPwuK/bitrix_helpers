<?php
namespace Itb\User\Services;

use Itb\Exceptions\ChangePasswordException;
use Itb\Exceptions\InvalidPasswordException;
use Itb\User\UserRepository;
use Itb\User\User;
use Itb\User\UserValidator;

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
}
