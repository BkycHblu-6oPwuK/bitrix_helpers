<?php
declare(strict_types=1);
namespace Beeralex\User\Auth\Contracts;

use Bitrix\Main\Result;

interface EmailAuthenticatorContract extends AuthenticatorContract
{
    public function authenticateByEmail(string $email, string $password): Result;
}