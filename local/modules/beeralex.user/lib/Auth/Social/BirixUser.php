<?php

namespace Beeralex\User\Auth\Social;

class BirixUser extends AbstractAuthUser
{
    public function __construct(
        private int $id,
        private string $firstName,
        private string $lastName,
        private string $username,
        private string $photoUrl,
        private int $authDate,
        private string $loginPrefix
    ) {}

    public function getExternalId(): string
    {
        return (string)$this->id;
    }
    public function getLogin(): string
    {
        return $this->loginPrefix . '_' . ($this->username ?: $this->id);
    }
    public function getName(): string
    {
        return $this->firstName;
    }
    public function getLastName(): string
    {
        return $this->lastName;
    }
    public function getEmail(): ?string
    {
        return null;
    }
}
