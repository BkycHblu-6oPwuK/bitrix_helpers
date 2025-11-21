<?php

declare(strict_types=1);

namespace Beeralex\User\Auth\Validator;

use Beeralex\User\Auth\Contracts\AuthValidatorInterface;
use Beeralex\User\Validation\Validator\UniqueEmailValidator;
use Bitrix\Main\Error;
use Bitrix\Main\Validation\ValidationResult;
use Bitrix\Main\Validation\Validator\EmailValidator;

class AuthEmailValidator implements AuthValidatorInterface
{
    public function validateForRegistration(\Beeralex\User\Auth\AuthCredentialsDto $credentials): ValidationResult
    {
        $result = new ValidationResult();
        foreach ($this->validateEmail($credentials->getEmail())->getErrors() as $error) {
            $result->addError(new Error($error->getMessage(), 'email'));
        }
        if ($result->isSuccess()) {
            foreach ($this->validateUniqueEmail($credentials->getEmail())->getErrors() as $error) {
                $result->addError(new Error($error->getMessage(), 'email'));
            }
        }
        foreach ($this->validatePassword($credentials->getPassword())->getErrors() as $error) {
            $result->addError(new Error($error->getMessage(), 'password'));
        }
        return $result;
    }

    public function validateForLogin(\Beeralex\User\Auth\AuthCredentialsDto $credentials): ValidationResult
    {
        $result = new ValidationResult();
        foreach ($this->validateEmail($credentials->getEmail())->getErrors() as $error) {
            $result->addError(new Error($error->getMessage(), 'email'));
        }
        foreach ($this->validatePassword($credentials->getPassword())->getErrors() as $error) {
            $result->addError(new Error($error->getMessage(), 'password'));
        }
        return $result;
    }

    protected function validateEmail(string $email): ValidationResult
    {
        $emailValivator = new EmailValidator(true);
        return $emailValivator->validate($email);
    }

    protected function validateUniqueEmail(string $email): ValidationResult
    {
        $uniqueEmailValidator = new UniqueEmailValidator();
        return $uniqueEmailValidator->validate($email);
    }

    protected function validatePassword(string $password): ValidationResult
    {
        $notEmptyValidator = new \Bitrix\Main\Validation\Validator\NotEmptyValidator();
        return $notEmptyValidator->validate($password);
    }
}
