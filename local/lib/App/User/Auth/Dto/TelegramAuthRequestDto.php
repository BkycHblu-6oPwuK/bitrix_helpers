<?php
namespace App\User\Auth\Dto;

use App\User\Auth\Authenticators\TelegramAuthenticator;
use App\User\Dto\BaseUserDto;
use Bitrix\Main\Validation\Rule\NotEmpty;

class TelegramAuthRequestDto extends BaseUserDto
{
    #[NotEmpty(errorMessage: 'Не передан id пользователя в telegramm')]
    public ?string $externalId = null;
    public ?string $service = null;

    public function __construct()
    {
        $this->service = TelegramAuthenticator::getKey();
    }
}