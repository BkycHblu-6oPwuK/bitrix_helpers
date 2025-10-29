<?php

namespace Beeralex\User\Auth\Social\Contracts;

interface AuthUserInterface
{
    public function getLogin(): string;
    public function toBitrixArray(): array;
}
