<?php
declare(strict_types=1);

namespace Beeralex\User\Auth;

use Beeralex\Core\Http\Resources\Resource;

/**
 * DTO для передачи данных аутентификации/регистрации.
 * Позволяет работать с "размытыми" входными данными (массив),
 * предоставляя типизированный доступ к стандартным полям.
 */
class AuthCredentialsDto extends Resource
{
    public string $type = '';

    public static function make(array $data): static
    {
        $type = (string)($data['type'] ?? '');
        unset($data['type']);
        $dto = new static($data);
        $dto->type = $type;
        return $dto;
    }

    public function getEmail(): ?string
    {
        return $this->getString('email');
    }

    public function getPassword(): ?string
    {
        return $this->getString('password');
    }

    public function getCode(): ?string
    {
        return $this->getString('code');
    }

    public function getCodeVerify(): ?string
    {
        return $this->getString('code_verify') ?? $this->getString('code');
    }

    public function getName(): ?string
    {
        return $this->getString('name') ?? $this->getString('first_name');
    }

    public function getFirstName(): ?string
    {
        return $this->getString('first_name') ?? $this->getString('name');
    }

    public function getLastName(): ?string
    {
        return $this->getString('last_name');
    }

    public function getPhone(): ?string
    {
        return $this->getString('phone');
    }

    public function getGroup(): ?array
    {
        $val = $this->__get('group');
        return is_array($val) ? $val : null;
    }
}
