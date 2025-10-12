<?php
namespace App\User\Auth\Contracts;

use App\User\User;

interface PhoneAuthenticatorContract extends AuthenticatorContract
{
    public function authenticateByEmail(string $email, string $password): ?User;
}
