<?php
namespace App\Main\Request;

use Bitrix\Main\Validation\ValidationResult;

interface RequestDtoInterface
{
    public static function fromArray(array $data): self;
    public function getData(): array;
    public function isValid(): bool;
    public function getErrors(): array;
    public function setValidationResult(ValidationResult $result): static;
}
