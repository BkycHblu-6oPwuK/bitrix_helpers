<?php

namespace Beeralex\User\Auth\Social\Services;

use Beeralex\User\Auth\Social\Contracts\AuthUserInterface;

abstract class AbstractAuthService
{
    abstract public function verify(array $data): bool;
    abstract public function getUser(array $data): AuthUserInterface;
}
