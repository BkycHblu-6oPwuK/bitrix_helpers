<?php
declare(strict_types=1);
namespace Beeralex\User\Validation\Rule;

use Beeralex\User\Validation\Validator\UniqueEmailValidator;
use Bitrix\Main\Validation\Rule\AbstractPropertyValidationAttribute;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class UniqueEmailRule extends AbstractPropertyValidationAttribute
{
    public function __construct(
        protected string|\Bitrix\Main\Localization\LocalizableMessageInterface|null $errorMessage = 'Пользователь с таким Email уже зарегистрирован'
    ) 
    {}

    public function getValidators() : array
    {
        return [
            new UniqueEmailValidator()
        ];
    }
}
