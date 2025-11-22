<?php
declare(strict_types=1);
namespace Beeralex\User\Contracts;

use Beeralex\User\Enum\Gender;
use Beeralex\User\Phone;
use Bitrix\Main\Type\Date;

interface UserEntityContract
{
    public function isAuthorized(): bool;
    public function isAdmin(): bool;
    public function getId(): ?int;
    public function getName(): string;
    public function getLastName(): string;
    public function getPatronymic(): string;
    public function getPhoneAsString(): string;
    public function getPhone(): ?Phone;
    public function getEmail(): string;
    public function getPassword(): string;
    public function getCheckword(): string;
    public function getBirthday(): ?Date;
    public function getFullName(): string;
    public function getPhoto(): ?string;
    public function getGender() : ?Gender;
    public function getFields(): array;
    public function get(string $key): mixed;
    public function setId(int $id): void;
    public function getUserGroup() :array;
}
