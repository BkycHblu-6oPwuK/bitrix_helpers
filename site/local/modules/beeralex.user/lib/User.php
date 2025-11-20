<?php
declare(strict_types=1);
namespace Beeralex\User;

use Beeralex\User\Contracts\UserEntityContract;
use Beeralex\User\Contracts\UserRepositoryContract;
use Bitrix\Main\Type\Date;
use Beeralex\User\Enum\Gender;
use Beeralex\User\Phone;

class User implements UserEntityContract
{
    protected array $fields = [];
    protected ?Phone $phone = null;

    public function __construct(array $fields)
    {
        $this->fields = $fields;
        $this->buildPhone();
    }

    protected function buildPhone(): void
    {
        if (isset($this->fields['phone']) || !$this->fields['PERSONAL_PHONE']) {
            return;
        }

        $this->phone = Phone::fromString($this->fields['PERSONAL_PHONE']);
        $this->fields['phone'] = $this->phone;
    }

    public function isAuthorized(): bool
    {
        global $USER;
        return $USER->IsAuthorized() && $this->getId() == $USER->GetID();
    }

    public function isAdmin(): bool
    {
        global $USER;
        return $USER->IsAdmin();
    }

    public function getId(): ?int
    {
        return (int)$this->fields['ID'] ?: null;
    }

    public function getName(): string
    {
        return $this->fields['NAME'] ?? '';
    }

    public function getLastName(): string
    {
        return $this->fields['LAST_NAME'] ?? '';
    }

    public function getPatronymic(): string
    {
        return $this->fields['SECOND_NAME'] ?? '';
    }

    public function getPhoneAsString(): string
    {
        return $this->getPhone()?->formatE164() ?? '';
    }

    public function getPhone(): ?Phone
    {
        return $this->phone;
    }

    public function getEmail(): string
    {
        return $this->fields['EMAIL'] ?? '';
    }

    public function getPassword(): string
    {
        return $this->fields['PASSWORD'] ?? '';
    }

    public function getBirthday(): ?Date
    {
        $birthday = $this->fields['PERSONAL_BIRTHDAY'];
        return $birthday ? new Date($this->fields['PERSONAL_BIRTHDAY']) : null;
    }

    public function getFullName(): string 
    {
        $fullname = '';
        if($name = $this->getName()){
            $fullname .= $name . ' ';
        }
        if($lastName = $this->getLastName()){
            $fullname .= $lastName;
        }
        return $fullname;
    }

    public function getPhoto(): ?string
    {
        return $this->fields['UF_PHOTO'];
    }

    public function getGender() : ?Gender
    {
        return Gender::tryFrom($this->fields['PERSONAL_GENDER']);
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function setId(int $id): void
    {
        $this->fields['ID'] = $id;
    }

    public function getUserGroup() :array
    {
        return \CUser::GetUserGroup($this->getId());
    }

    public function get(string $key): mixed
    {
        return $this->fields[$key] ?? null;
    }
}
