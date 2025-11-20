<?php
declare(strict_types=1);
namespace Beeralex\User\Validation\Validator;

use Beeralex\User\Contracts\UserRepositoryContract;
use Beeralex\User\Phone;
use Bitrix\Main\Error;
use Bitrix\Main\Validation\ValidationResult;
use Bitrix\Main\Validation\Validator\ValidatorInterface;

class UniquePhoneValidator implements ValidatorInterface
{
    public function __construct() {}

    public function validate(mixed $value) : ValidationResult
    {
        $result = new ValidationResult();

        if (service(UserRepositoryContract::class)->getByPhone(Phone::fromString($value))) {
            $result->addError(new Error('Пользователь с таким телефоном уже зарегистрирован'));
        }

        return $result;
    }
}