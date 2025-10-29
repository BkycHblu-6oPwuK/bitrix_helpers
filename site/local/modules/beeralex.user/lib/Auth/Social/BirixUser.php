<?php

namespace Beeralex\User\Auth\Social;

use Beeralex\User\Auth\Social\Contracts\AuthUserInterface;

class BirixUser implements AuthUserInterface
{
    public function __construct(
        public readonly int $id,
        public readonly string $firstName,
        public readonly string $lastName,
        public readonly string $username,
        public readonly string $email,
        public readonly string $photoUrl,
        public readonly int $authDate,
        public readonly string $externalAuthId,
        public readonly string $loginPrefix
    ) {}
    
    public function toBitrixArray(): array
    {
        return [
            'XML_ID' => $this->externalAuthId,
            'LOGIN' => $this->getLogin(),
            'NAME' => $this->firstName,
            'LAST_NAME' => $this->lastName,
            'EMAIL' => $this->email,
            'EXTERNAL_AUTH_ID' => $this->externalAuthId,
        ];
    }

        public function getLogin(): string
    {
        return $this->loginPrefix . '_' . ($this->username ?: $this->id);
    }
}
