<?php
namespace App\User\Auth\Contracts;

use App\User\Phone\Phone;

interface PhoneAuthenticatorContract extends AuthenticatorContract
{
    public function authenticateByPhone(Phone $phone): void;
}
