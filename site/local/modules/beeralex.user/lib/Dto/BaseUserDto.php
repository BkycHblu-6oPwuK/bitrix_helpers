<?php
declare(strict_types=1);
namespace Beeralex\User\Dto;

use Beeralex\Core\Http\Request\AbstractRequestDto;

abstract class BaseUserDto extends AbstractRequestDto
{
    public string $email = '';
    public string $password = '';
    public ?string $first_name = '';
    public ?string $last_name = '';
    public ?string $phone = null;
    public ?string $codeVerify = null;
    public ?array $group = null;
}
