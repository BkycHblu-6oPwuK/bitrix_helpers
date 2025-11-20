<?php
declare(strict_types=1);

namespace Beeralex\User\Dto;

use Beeralex\Core\Http\Request\AbstractRequestDto;

/**
 * DTO для передачи данных аутентификации/регистрации.
 * Позволяет работать с "размытыми" входными данными (массив),
 * предоставляя типизированный доступ к стандартным полям.
 */
class AuthCredentialsDto extends AbstractRequestDto
{
    public string $type = '';
    public array $payload = [];

    public static function fromArray(array $data): static
    {
        $dto = new static();
        $dto->type = (string)($data['type'] ?? '');
        unset($data['type']);
        $dto->payload = $data;
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
        $val = $this->get('group');
        return is_array($val) ? $val : null;
    }

    public function getString(string $key): ?string
    {
        $value = $this->payload[$key] ?? null;
        return is_string($value) ? $value : null;
    }

    public function get(string $key): mixed
    {
        return $this->payload[$key] ?? null;
    }
}
