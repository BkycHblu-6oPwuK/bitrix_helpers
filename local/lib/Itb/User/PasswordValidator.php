<?php

namespace Itb\User;

use Bitrix\Main\Security\Password;
use Itb\User\UserRepository;

class PasswordValidator
{
    /**
     * Проверяет соответствует ли пароль $password его хешу $passwordHash, хранимому в таблице b_user
     *
     * @param string $password     настоящий пароль
     * @param string $passwordHash хэш пароля
     *
     * @return bool соответствует ли пароль хэшу
     */
    public function validatePassword(string $password, string $passwordHash): bool
    {
        return Password::equals($passwordHash,$password);
    }


    /**
     * Проверяет соответствует ли $ceckword его хешу $checkwordHash, хранимому в таблице b_user
     *
     * @param string $checkword
     * @param string $checkwordHash
     *
     * @return bool
     */
    public function validateCheckword(string $checkword, string $checkwordHash): bool
    {
        return Password::equals($checkwordHash,$checkword);
    }


    /**
     * @param string $password пароль пользователя
     *
     * @return bool правильный ли пароль
     */
    public function checkCurrentUserPassword(string $password): bool
    {
        return $this->validatePassword($password, $this->getCurrentUserPasswordHash());
    }


    /**
     * @return string Хэш пароля из БД для текущего пользователя
     */
    private function getCurrentUserPasswordHash(): string
    {
        return (new UserRepository)->getById(User::current()->GetID())->getPassword();
    }
}
