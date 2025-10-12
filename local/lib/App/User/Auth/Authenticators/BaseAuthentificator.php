<?php

namespace App\User\Auth\Authenticators;

use App\User\Exceptions\RegistrationException;
use App\User\User;
use App\User\UserRepository;

abstract class BaseAuthentificator
{
    public function __construct(
        protected readonly UserRepository $repository
    ) {}

    public function authorizeByUserId(int $userId): void
    {
        (new \CUser())->Authorize($userId);
    }

    public function register(User $user): void
    {
        try {
            $id = (new UserRepository())->add($user);
        } catch (\Exception $e) {
            throw new RegistrationException($e->getMessage(), 0, $e);
        }

        $this->authorizeByUserId($id);
    }
}
