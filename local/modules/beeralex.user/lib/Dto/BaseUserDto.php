<?php

namespace Beeralex\User\Dto;

use Beeralex\Core\Http\Request\AbstractRequestDto;

abstract class BaseUserDto extends AbstractRequestDto
{
    /** id в внешнем сервисе */
    public int|string $id = '';
    public string $email = '';
    public string $password = '';
    public ?string $first_name = '';
    public ?string $last_name = '';
    public ?string $phone = null;
    public ?int $codeVerify = null;
    public ?array $group = null;
}
