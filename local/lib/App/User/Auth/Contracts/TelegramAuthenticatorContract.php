<?php
namespace App\User\Auth\Contracts;

use App\User\User;

interface TelegramAuthenticatorContract extends AuthenticatorContract
{
    public function authenticateByEmail(string $email, string $password): ?User;
}
