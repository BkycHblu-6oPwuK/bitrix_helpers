<?php
namespace Beeralex\User\Auth\Authenticators;

use Beeralex\User\Auth\AuthCredentialsDto;
use Bitrix\Main\Result;

class EmptyAuthentificator extends AbstractAuthentificator
{
    public function getKey(): string
    {
        return 'empty';
    }

    public function authenticate(AuthCredentialsDto $credentials): Result
    {
        $result = new Result();
        $result->addError(new \Bitrix\Main\Error("Empty authentificator cannot authenticate users", 'authentificator'));
        return $result;
    }

    public function getTitle(): string
    {
        return 'Empty Authentificator';
    }
}