<?php
declare(strict_types=1);
namespace Beeralex\User\Validation\Validator;

use Beeralex\User\Dto\AuthCredentialsDto;
use Bitrix\Main\Validation\ValidationResult;
use Bitrix\Main\Validation\Validator\EmailValidator;
use Bitrix\Main\Validation\Validator\PhoneValidator;
use Bitrix\Main\Validation\Validator\ValidatorInterface;

class EmailRegisterValidator implements ValidatorInterface
{
    public function __construct() {}

    /**
     * @param AuthCredentialsDto $value
     */
    public function validate(mixed $value) : ValidationResult
    {
        $result = new ValidationResult();
        $emailValivator = new EmailValidator(true);
        $notEmptyValidator = new \Bitrix\Main\Validation\Validator\NotEmptyValidator();
        if($emailValidationResult = $emailValivator->validate($value->getEmail())) {
            foreach($emailValidationResult->getErrors() as $error) {
                $result->addError($error);
            }
            if($result->isSuccess()) {
                $uniqueEmailValidator = new UniqueEmailValidator();
                $uniqueEmailValidationResult = $uniqueEmailValidator->validate($value->getEmail());
                foreach($uniqueEmailValidationResult->getErrors() as $error) {
                    $result->addError($error);
                }
            }
        }
        if($notEmptyValidationResult = $notEmptyValidator->validate($value->getPassword())) {
            foreach($notEmptyValidationResult->getErrors() as $error) {
                $result->addError($error);
            }
        }

        return $result;
    }
}