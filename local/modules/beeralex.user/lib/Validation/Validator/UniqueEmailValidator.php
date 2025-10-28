<?php
declare(strict_types=1);

namespace Beeralex\User\Validation\Validator;

use Beeralex\User\Contracts\UserRepositoryContract;
use Bitrix\Main\Config\Option;
use Bitrix\Main\Error;
use Bitrix\Main\Validation\ValidationResult;
use Bitrix\Main\Validation\Validator\ValidatorInterface;

class UniqueEmailValidator implements ValidatorInterface
{
    public function __construct() {}

    public function validate(mixed $value) : ValidationResult
    {
        $result = new ValidationResult();

        if (Option::get('main', 'new_user_email_uniq_check', 'Y') === 'Y' && service(UserRepositoryContract::class)->getByEmail($value)) {
            $result->addError(new Error('Пользователь с таким email уже зарегистрирован'));
        }

        return $result;
    }
}