<?php
namespace App\User\Auth\Contracts;

use App\User\User;

interface AuthenticatorContract
{
    public static function getKey(): string;

    public function authenticate(array $credentials): void;

    public function register(array $data): void;
}
