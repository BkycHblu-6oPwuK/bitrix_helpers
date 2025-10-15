<?php
namespace App\User\Auth\Dto;

use App\User\Dto\BaseUserDto;
use Bitrix\Main\Validation\Rule\Email;
use Bitrix\Main\Validation\Rule\Length;
use App\User\Validation\Rule\UniqueEmailRule;
use Bitrix\Main\Validation\Rule\NotEmpty;

class EmailRegisterRequestDto extends BaseUserDto
{
    #[UniqueEmailRule]
    #[Email(errorMessage: 'Некорректный email')]
    #[NotEmpty(errorMessage: 'Email обязателен')]
    public string $email = '';

    #[NotEmpty(errorMessage: 'Пароль обязателен')]
    #[Length(min: 6, max: 50, errorMessage: 'Пароль должен быть от 6 до 50 символов')]
    public string $password = '';

    #[NotEmpty(errorMessage: 'Имя обязательно')]
    public string $name = '';
}