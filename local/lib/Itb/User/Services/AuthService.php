<?php

namespace Itb\User\Services;

use Itb\User\Exceptions\IncorrectOldPasswordException;
use Itb\User\Exceptions\RegistrationException;
use Itb\User\Exceptions\UserNotFoundException;
use Itb\User\UserRepository;
use Itb\User\PasswordValidator;
use Itb\User\User;
use Itb\User\UserValidator;

class AuthService
{

    /**
     * Авторизует пользователя по данному емаилу и паролю
     *
     * @param string $email
     * @param string $password
     *
     * @throws IncorrectOldPasswordException
     * @throws UserNotFoundException
     */
    public function authorize(string $email, string $password): void
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

        \CUser::SendUserInfo($user->getId(), 's1', '', false, 'USER_PASS_REQUEST');
    }


    /**
     * Регистрация нового пользователя.
     *
     * @param User  $user
     * @param array $subscriptionIds
     *
     * @throws \Bitrix\Main\ArgumentException
     */
    public function register(User $user): void
    {
        // валидируем профиль

        if (!(new UserValidator())->validateUser($user, true)) {
            throw new \InvalidArgumentException('Given profile data is invalid');
        }

        // добавляем нового пользователя

        try {
            $id = (new UserRepository())->add($user);
            $this->createNotificationsPreference($id);
        } catch (\Exception $e) {
            throw new RegistrationException($e->getMessage(), 0, $e);
        }

        // и авторизуемся под ним

        $this->authorizeByUserId($id);
    }

    /**
     * Авторизует под пользователем с данным ID
     *
     * @param int $userId
     */
    public function authorizeByUserId(int $userId): void
    {
        (new \CUser())->Authorize($userId);
    }

    protected function createNotificationsPreference(int $userId) : void
    {
        (new \Itb\Notification\Services\NotificationPreferenceService)->createDefault($userId);
    }
}
