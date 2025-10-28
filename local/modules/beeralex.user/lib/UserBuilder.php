<?php

namespace Beeralex\User;

use Bitrix\Main\Context;
use Beeralex\Core\Helpers\UserHelper;
use Beeralex\User\Contracts\UserBuilderContract;
use Beeralex\User\Contracts\UserFactoryContract;
use Beeralex\User\Phone;

class UserBuilder implements UserBuilderContract
{
    protected ?string $email = null;
    protected string $name = '';
    protected string $lastName = '';
    protected ?string $password = null;
    protected ?array $group = null;
    protected ?Phone $phone = null;
    protected string $externalId = '';
    protected string $authKey = '';

    public function __construct(protected readonly UserFactoryContract $factory) {}

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

    public static function fromDto(\Beeralex\User\Dto\BaseUserDto $dto): self
    {
        $builder = service(UserBuilderContract::class);

        if (!empty($dto->email)) {
            $builder->setEmail($dto->email);
        }

        if (!empty($dto->first_name)) {
            $builder->setName($dto->first_name);
        }

        if (!empty($dto->last_name)) {
            $builder->setLastName($dto->last_name);
        }

        if (!empty($dto->password)) {
            $builder->setPassword($dto->password);
        }

        if (!empty($dto->group)) {
            $builder->setGroup($dto->group);
        }

        if (!empty($dto->phone)) {
            $builder->setPhone(Phone::fromString($dto->phone));
        }

        return $builder;
    }

    public function build(): User
    {
        if (!$this->email && $this->phone) {
            $this->email = $this->phone->getRaw() . '@example.com';
        } elseif ($this->externalId) {
            $this->email = $this->authKey . $this->externalId . '@example.com';
        }
        if (!$this->email) {
            throw new \InvalidArgumentException("Email обязателен");
        }
        if (!$this->password) {
            $this->password = UserHelper::generatePassword();
        }
        return $this->factory->create([
            'EMAIL'            => $this->email,
            'LOGIN'            => $this->email,
            'PASSWORD'         => $this->password,
            'NAME'             => $this->name,
            'LAST_NAME'        => $this->lastName,
            'PERSONAL_PHONE'   => $this->phone?->formatE164() ?? '',
            'CONFIRM_PASSWORD' => $this->password,
            'ACTIVE' => 'Y',
            'LID' => Context::getCurrent()->getSite(),
            'GROUP_ID' => $this->group ?? UserHelper::getDefaultUserGroups(),
        ]);
    }
}
