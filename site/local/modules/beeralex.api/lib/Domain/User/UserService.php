<?php

declare(strict_types=1);

namespace Beeralex\Api\Domain\User;

use Beeralex\User\UserRepository;
use Bitrix\Main\HttpRequest;

/**
 * Сервис для работы с пользователями в контексте API
 */
class UserService
{
    public function extractJwtToken(HttpRequest $request): ?string
    {
        $auth = $request->getHeader('Authorization');
        if ($auth && preg_match('/Bearer\s+(\S+)/', $auth, $m)) {
            return $m[1];
        }

        $cookie = $request->getCookie("access");
        if ($cookie) {
            return $cookie;
        }

        return null;
    }

    public function extractRefreshToken(HttpRequest $request): ?string
    {
        $cookie = $request->getCookie("refresh");
        if ($cookie) {
            return $cookie;
        }

        return null;
    }

    public function getCurrentUserDTO(): UserDTO
    {
        $user = \service(UserRepository::class)->getCurrentUser();
        return UserDTO::make([
            'id' => $user->getId(),
            'name' => $user->getName(),
            'lastName' => $user->getLastName(),
        ]);
    }
}
