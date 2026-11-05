<?php

declare(strict_types=1);

namespace Beeralex\Api\Domain\User;

use Beeralex\User\Contracts\UserRepositoryContract;

/**
 * Сервис для работы с пользователями в контексте API
 */
class UserService
{
    public function getCurrentUserDTO(): UserDTO
    {
        $user = \service(UserRepositoryContract::class)->getCurrentUser();
        return UserDTO::make([
            'id' => $user->getId(),
            'name' => $user->getName(),
            'lastName' => $user->getLastName(),
        ]);
    }
}
