<?php
declare(strict_types=1);
namespace Beeralex\User\Auth\Contracts;

interface EmailAuthenticatorContract extends AuthenticatorContract
{
    public function authenticateByEmail(string $email, string $password): void;
}