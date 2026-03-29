<?php
declare(strict_types=1);

namespace Beeralex\Api\Domain\User;

/**
 * @property int $id
 * @property string $name
 * @property string $lastName
 */
class UserDTO extends \Beeralex\Core\Http\Resources\Resource
{
    public static function make(array $data): static
    {
        return new static([
            'id' => $data['id'] ?? $data['userId'] ?? 0,
            'name' => $data['name'] ?? '',
            'lastName' => $data['lastName'] ?? '',
        ]);
    }
}