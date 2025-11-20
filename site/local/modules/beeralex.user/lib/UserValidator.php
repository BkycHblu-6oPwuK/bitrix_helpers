<?php
declare(strict_types=1);
namespace Beeralex\User;

class UserValidator
{
    /**
     * Проверяет, что пользователь с таким email или телефоном уже существует.
     * @throws UserAlreadyExistsException
     */
    public static function validateUniqueEmailAndPhone(?string $email, ?Phone $phone): void
    {
        if ($email !== null && User::existsByEmail($email)) {
            throw new UserAlreadyExistsException('User with this email already exists');
        }
        if ($phone !== null && User::existsByPhone($phone)) {
            throw new UserAlreadyExistsException('User with this phone already exists');
        }
    }
}