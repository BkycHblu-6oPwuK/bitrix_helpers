<?php

namespace App\User;

use Bitrix\Main\Context;
use Beeralex\Core\Helpers\UserHelper;
use App\User\Phone\Phone;

class UserBuilder
{
    private ?string $email = null;
    private string $name = '';
    private string $lastName = '';
    private ?string $password = null;
    private ?array $group = null;
    private ?Phone $phone = null;
    private string $externalId = '';
    private string $authKey = '';

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function setPhone(Phone $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function setGroup(?array $group): self
    {
        $this->group = $group;
        return $this;
    }

    public function setExternalId(string $externalId): self
    {
        $this->externalId = $externalId;
        return $this;
    }

    public function setAuthentificatorKey(string $authKey): self
    {
        $this->authKey = $authKey;
        return $this;
    }

    public static function fromDto(\App\User\Dto\BaseUserDto $dto): self
    {
        $builder = new self();

        if (!empty($dto->email)) {
            $builder->setEmail($dto->email);
        }

        if (!empty($dto->name)) {
            $builder->setName($dto->name);
        }

        if (!empty($dto->lastName)) {
            $builder->setLastName($dto->lastName);
        }

        if (!empty($dto->password)) {
            $builder->setPassword($dto->password);
        }

        if (!empty($dto->group)) {
            $builder->setGroup($dto->group);
        }

        if (!empty($dto->phone)) {
            $builder->setPhone(
                $dto->phone instanceof Phone
                    ? $dto->phone
                    : new Phone($dto->phone)
            );
        }

        return $builder;
    }

    public function build(): User
    {
        if (!$this->email && $this->phone) {
            $this->email = $this->phone->getNumber() . '@example.com';
        } elseif($this->externalId) {
            $this->email = $this->authKey . $this->externalId .'@example.com';
        }
        if(!$this->email) {
            throw new \InvalidArgumentException("Email обязателен");
        }
        if (!$this->password) {
            $this->password = UserHelper::generatePassword();
        }
        return new User([
            'EMAIL'            => $this->email,
            'LOGIN'            => $this->email,
            'PASSWORD'         => $this->password,
            'NAME'             => $this->name,
            'LAST_NAME'        => $this->lastName,
            'PERSONAL_PHONE'   => $this->phone?->getNumber() ?? '',
            'CONFIRM_PASSWORD' => $this->password,
            'ACTIVE' => 'Y',
            'LID' => Context::getCurrent()->getSite(),
            'GROUP_ID' => $this->group ?? UserHelper::getDefaultUserGroups(),
        ]);
    }
}
