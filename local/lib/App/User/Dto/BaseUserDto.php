<?php

namespace App\User\Dto;

use Beeralex\Core\Http\Request\AbstractRequestDto;

abstract class BaseUserDto extends AbstractRequestDto
{
    public string $email = '';
    public string $password = '';
    public string $name = '';
    public string $lastName = '';
    public ?string $phone = null;
    public ?array $group = null;
    public ?string $externalId = null;
    /** Название внешнего сервиса (telegram, google, facebook и т.д.) */
    public ?string $service = null;
}
