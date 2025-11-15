<?php
declare(strict_types=1);
namespace Beeralex\User\Auth\Social\Contracts;

interface AuthUserInterface
{
    public function getLogin(): string;
    public function toBitrixArray(): array;
}
