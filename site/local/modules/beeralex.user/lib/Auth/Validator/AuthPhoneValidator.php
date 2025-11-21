<?php
declare(strict_types=1);
namespace Beeralex\User\Auth\Validator;

use Beeralex\User\Auth\Contracts\AuthValidatorInterface;
use Beeralex\User\Validation\Validator\UniquePhoneValidator;
use Bitrix\Main\Error;
use Bitrix\Main\Validation\ValidationResult;
use Bitrix\Main\Validation\Validator\PhoneValidator;

class AuthPhoneValidator implements AuthValidatorInterface
{
    public function validateForRegistration(\Beeralex\User\Auth\AuthCredentialsDto $credentials): ValidationResult
    {
        $result = new ValidationResult();
        foreach ($this->validatePhone($credentials->getPhone())->getErrors() as $error) {
            $result->addError(new Error($error->getMessage(), 'phone'));
        }
        if ($result->isSuccess()) {
            foreach ($this->validateUniquePhone($credentials->getPhone())->getErrors() as $error) {
                $result->addError(new Error($error->getMessage(), 'phone'));
            }
        }
        return $result;
    }

    public function validateForLogin(\Beeralex\User\Auth\AuthCredentialsDto $credentials): ValidationResult
    {
        $result = new ValidationResult();
        foreach ($this->validatePhone($credentials->getPhone())->getErrors() as $error) {
            $result->addError(new Error($error->getMessage(), 'phone'));
        }
        return $result;
    }

    protected function validatePhone(string $phone): ValidationResult
    {
        $phoneValivator = new PhoneValidator();
        return $phoneValivator->validate($phone);
    }

    protected function validateUniquePhone(string $phone): ValidationResult
    {
        $uniquePhoneValidator = new UniquePhoneValidator();
        return $uniquePhoneValidator->validate($phone);
    }
}