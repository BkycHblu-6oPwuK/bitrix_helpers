<?php

namespace App\User\Auth\Dto;

use App\User\Dto\BaseUserDto;
use Bitrix\Main\Validation\Rule\NotEmpty;

class TelegramAuthRequestDto extends BaseUserDto
{
    #[NotEmpty(errorMessage: 'Не передан id пользователя в telegramm')]
    public int|string $id;
    public ?string $first_name = null;
    public ?string $last_name = null;
    public ?string $username = null;
    public ?string $photo_url = null;
    public ?int $auth_date = null;
    public ?string $hash = null;

    /**
     * Возвращает данные в том виде, как их требует Telegram для проверки подписи.
     */
    public function getDataForValidation(): array
    {
        $data = [];

        foreach (
            [
                'auth_date',
                'first_name',
                'id',
                'last_name',
                'photo_url',
                'username',
            ] as $key
        ) {
            if (isset($this->{$key}) && $this->{$key} !== null) {
                $data[$key] = $this->{$key};
            }
        }

        return $data;
    }
}
