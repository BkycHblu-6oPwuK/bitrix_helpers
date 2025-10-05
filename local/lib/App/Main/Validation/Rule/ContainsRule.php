<?php
declare(strict_types=1);

namespace App\Main\Validation\Rule;

use Bitrix\Main\Validation\Rule\AbstractPropertyValidationAttribute;
use App\Validation\Validator\ContainsValidator;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ContainsRule extends AbstractPropertyValidationAttribute
{
    public function __construct(
        private readonly string $contains,
        protected ?string $errorMessage = null
    ) 
    {}

    public function getValidators() : array
    {
        return [
            new ContainsValidator($this->contains)
        ];
    }
}