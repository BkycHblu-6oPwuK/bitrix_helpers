<?php

declare(strict_types=1);

namespace Beeralex\User;

use Beeralex\Core\Service\UserService as CoreUserService;
use Beeralex\User\Auth\Authenticators\EmptyAuthentificator;
use Beeralex\User\Contracts\UserRepositoryContract;
use Beeralex\User\User;
use Bitrix\Main\Application;
use Bitrix\Main\Error;
use Bitrix\Main\Result;
use Bitrix\Main\Security\Password;

class UserService extends CoreUserService
{
    public function __construct(
        protected readonly UserRepositoryContract $userRepository
    )
    {
        parent::__construct();
    }
    /**
     * Меняет пароль у пользователя
     * Можно вызвать только для зарегистрированного пользователя с указанным ID
     */
    public function changePassword(User $user, string $password): Result
    {
        $result = new Result();
        if (!$this->validatePassword($password, $user->getUserGroup())) {
            $result->addError(new Error('Password does not meet the policy requirements', 'password'));
            return $result;
        }

        try {
            $this->userRepository->update($user->getId(), [
                'PASSWORD' => $password
            ]);
        } catch (\Exception $e) {
            $result->addError(new Error($e->getMessage(), 'password'));
        }
        return $result;
    }

    /**
     * Восстановление пароля пользователя
     */
    public function restorePassword(string $email): Result
    {
        $result = new Result();
        $user = $this->userRepository->getByEmail($email);
        if (!$user) {
            $result->addError(new Error("User with email {$email} not found", 'email'));
            return $result;
        }

        \CUser::SendUserInfo($user->getId(), SITE_ID, '', false, 'USER_PASS_REQUEST');
        return $result;
    }

    public function changePasswordByCheckword(string $email, string $password, string $checkword): Result
    {
        $result = new Result();
        $user = $this->userRepository->getByEmail($email);
        if (!$user) {
            $result->addError(new Error("User with email {$email} not found", 'email'));
            return $result;
        }

        if (!Password::equals($user->getCheckword(), $checkword)) {
            $result->addError(new Error("Invalid checkword", 'checkword'));
        }

        $this->changePassword($user, $password);

        service(EmptyAuthentificator::class)->authorizeByUserId($user->getId());

        return $result;
    }

    /**
     * Смена пароля у пользователя
     */
    public function changePasswordByOldPassword(User $user, string $newPassword, string $oldPassword): Result
    {
        $result = new Result();
        if (!Password::equals($user->getPassword(), $oldPassword)) {
            $result->addError(new Error("Old password is incorrect", 'oldPassword'));
        }

        $this->changePassword($user, $newPassword);
        return $result;
    }

    /**
     * Обновление профиля на странице профиля
     */
    public function updateProfile(User $user, array $fields): Result
    {
        $result = new Result();
        $userId = $user->getId();
        if (!$userId) {
            $result->addError(new Error("Cannot update profile for unauthorized user", 'user'));
            return $result;
        }
        $conn = Application::getConnection();
        try {
            $conn->startTransaction();
            $this->userRepository->update($userId, $fields);
            $conn->commitTransaction();
        } catch (\Exception $e) {
            $conn->rollbackTransaction();
            $result->addError(new Error($e->getMessage(), 'profile'));
        }
        return $result;
    }
}
