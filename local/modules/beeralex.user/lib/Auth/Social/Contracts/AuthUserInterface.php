<?php
namespace Beeralex\User\Auth\Social\Contracts;

interface AuthUserInterface
{
    public function getExternalId(): string;
    public function getLogin(): string;
    public function getName(): string;
    public function getLastName(): string;
    public function getEmail(): ?string;
    public function toBitrixArray(): array;
}
