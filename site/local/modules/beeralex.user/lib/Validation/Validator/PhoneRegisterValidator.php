<?php
declare(strict_types=1);
namespace Beeralex\User\Validation\Validator;

use Beeralex\User\Dto\AuthCredentialsDto;
use Bitrix\Main\Validation\ValidationResult;
use Bitrix\Main\Validation\Validator\PhoneValidator;
use Bitrix\Main\Validation\Validator\ValidatorInterface;

class PhoneRegisterValidator implements ValidatorInterface
{
    public function __construct() {}

    /**
     * @param AuthCredentialsDto $value
     */
    public function validate(mixed $value) : ValidationResult
    {
        $result = new ValidationResult();
        $phoneValivator = new PhoneValidator();
        if($phoneValidationResult = $phoneValivator->validate($value->getPhone())) {
            foreach($phoneValidationResult->getErrors() as $error) {
                $result->addError($error);
            }
            if($result->isSuccess()) {
                $uniquePhoneValidator = new UniquePhoneValidator();
                $uniquePhoneValidationResult = $uniquePhoneValidator->validate($value->getPhone());
                foreach($uniquePhoneValidationResult->getErrors() as $error) {
                    $result->addError($error);
                }
            }
        }

        return $result;
    }
}