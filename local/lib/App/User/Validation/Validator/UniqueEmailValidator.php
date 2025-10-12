<?php
declare(strict_types=1);

namespace App\User\Validation\Validator;

use App\User\UserRepository;
use Bitrix\Main\Error;
use Bitrix\Main\Validation\ValidationResult;
use Bitrix\Main\Validation\Validator\ValidatorInterface;

class UniqueEmailValidator implements ValidatorInterface
{
    public function __construct() {}

    public function validate(mixed $value) : ValidationResult
    {
        $result = new ValidationResult();

        if ((new UserRepository())->getByEmail($value)) {
            $result->addError(new Error('Пользователь с таким email уже зарегистирован'));
        }

        return $result;
    }
}