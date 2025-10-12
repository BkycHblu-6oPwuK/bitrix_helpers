<?php
namespace App\User\Auth\Contracts;

use App\User\User;

interface EmailAuthenticatorContract extends AuthenticatorContract
{
    public function authenticateByEmail(string $email, string $password): void;
}