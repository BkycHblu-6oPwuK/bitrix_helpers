<?php
declare(strict_types=1);
namespace Beeralex\User\Auth\Contracts;

use Beeralex\User\Phone;
use Bitrix\Main\Result;

interface PhoneAuthentificatorContract extends AuthenticatorContract
{
    public function authenticateByPhone(Phone $phone, ?string $code = null): Result;
}
