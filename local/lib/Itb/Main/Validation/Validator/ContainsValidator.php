<?php
declare(strict_types=1);

namespace Itb\Main\Validation\Validator;

use Bitrix\Main\Error;
use Bitrix\Main\Validation\ValidationResult;
use Bitrix\Main\Validation\Validator\ValidatorInterface;

class ContainsValidator implements ValidatorInterface
{
    public function __construct(
        private readonly string $contains
    ) {}

    public function validate(mixed $value) : ValidationResult
    {
        $result = new ValidationResult();
        if(!is_string($value)){
            $result->addError(new Error('Значение должно быть строкой'));
        } elseif(!str_contains($value, $this->contains)){
            $result->addError(new Error('Искомая строка не найдена'));
        }
        return $result;
    }
}