<?php

namespace App\Main\Request;

use Bitrix\Main\Validation\ValidationResult;
use Bitrix\Main\Validation\ValidationService;

abstract class AbstractRequestDto implements RequestDtoInterface
{
    private ?ValidationResult $validationResult = null;

    public static function fromArray(array $data): self
    {
        $static = new static();
        foreach ($data as $key => $value) {
            if (property_exists($static, $key)) {
                $static->{$key} = $value;
            }
        }
        return $static;
    }

    public function getData(): array
    {
        return get_object_vars($this);
    }

    public function validate(): static
    {
        $validator = new ValidationService();
        $this->validationResult = $validator->validate($this);
        return $this;
    }

    public function setValidationResult(ValidationResult $result): static
    {
        $this->validationResult = $result;
        return $this;
    }

    public function getValidationResult(): ValidationResult
    {
        return $this->validationResult;
    }

    public function isValid(): bool
    {
        if ($this->validationResult === null) {
            $this->validate();
        }
        return $this->validationResult->isSuccess();
    }

    /**
     * @return \Bitrix\Main\Error[]
     */
    public function getErrors(): array
    {
        return $this->validationResult?->getErrors() ?? [];
    }
}
