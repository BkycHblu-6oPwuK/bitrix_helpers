<?php
namespace App\User\Auth\Contracts;

interface EmailAuthenticatorContract extends AuthenticatorContract
{
    public function authenticateByEmail(string $email, string $password): void;
}