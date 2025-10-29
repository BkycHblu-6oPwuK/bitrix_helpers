<?php

namespace Beeralex\User\Services;

use Bitrix\Main\Application;
use Beeralex\User\Exceptions\CheckwordValidationException;
use Beeralex\User\Exceptions\IncorrectOldPasswordException;
use Beeralex\User\Exceptions\UserNotFoundException;
use Beeralex\User\Repository\UserRepository;
use Beeralex\User\PasswordValidator;
use Beeralex\User\User;

class ProfileService
{
    /**
     * @var UserRepository
     */
    private $userRepository;
    /**
     * @var UserService
     */
    private $userService;


    public function __construct()
    {
        $this->userRepository = new UserRepository();
        $this->userService = new UserService();
    }

    /**
     * Смена пароля у пользователя
     *
     * @param string $email
     * @param string $password
     * @param string $checkword
     *
     * @throws ChangePasswordException
     * @throws InvalidPasswordException
     * @throws UserNotFoundException
     */
    public function changePasswordByCheckword(string $email, string $password, string $checkword): void
    {
        // проверяем есть ли пользователь с данным емаилом
        $user = $this->userRepository->getByEmail($email);
        if (!$user) {
            throw new UserNotFoundException('User not found by email ' . $email);
        }

        // проверяем чекворд у пользователя
        if (!(new PasswordValidator())->validateCheckword($checkword, $user->getCheckword())) {
            throw new CheckwordValidationException('Invalid checkword');
        }

        // обновляем пароль
        $this->userService->changePassword($user, $password);

        (new \CUser())->Authorize($user->getId());
    }

    /**
     * Смена пароля у пользователя
     *
     * @param User   $user
     * @param string $newPassword
     * @param string $oldPassword
     *
     * @throws IncorrectOldPasswordException
     * @throws ChangePasswordException
     * @throws InvalidPasswordException
     */
    public function changePasswordByOldPassword(User $user, string $newPassword, string $oldPassword): void
    {
        if (!(new PasswordValidator())->validatePassword($oldPassword, $user->getPassword())) {
            throw new IncorrectOldPasswordException('Old password is incorrect');
        }

        $this->userService->changePassword($user, $newPassword);
    }

    /**
     * Обновление профиля на странице профиля
     *
     * @param User $user
     * @param array $fields
     *
     * @throws \Bitrix\Main\Db\SqlQueryException
     * @throws \Exception
     */
    public function updateProfile(User $user, array $fields)
    {
        $userId = $user->getId();
        if (!$userId) {
            throw new UserNotFoundException();
        }
        $conn = Application::getConnection();
        try {
            $conn->startTransaction();
            $this->userRepository->update($userId, $fields);
            $conn->commitTransaction();
        } catch (\Exception $e) {
            $conn->rollbackTransaction();
            throw $e;
        }
    }
}
