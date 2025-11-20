<?php
declare(strict_types=1);
namespace Beeralex\User;

use Bitrix\Main\Context;
use Beeralex\Core\Helpers\UserHelper;
use Beeralex\Core\Service\UserService;
use Beeralex\User\Contracts\UserBuilderContract;
use Beeralex\User\Contracts\UserFactoryContract;
use Beeralex\User\Phone;
use Beeralex\User\Dto\AuthCredentialsDto;

class UserBuilder implements UserBuilderContract
{
    protected ?string $email = null;
    protected string $name = '';
    protected string $lastName = '';
    protected ?string $password = null;
    protected ?array $group = null;
    protected ?Phone $phone = null;
    protected string $authKey = '';

    public function __construct(protected readonly UserFactoryContract $factory, protected readonly UserService $userService) {}

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

    public function setAuthentificatorKey(string $authKey): self
    {
        $this->authKey = $authKey;
        return $this;
    }

    public static function fromDto(AuthCredentialsDto $dto): self
    {
        $builder = service(UserBuilderContract::class);

        if ($email = $dto->getEmail()) {
            $builder->setEmail($email);
        }

        if ($firstName = $dto->getFirstName()) {
            $builder->setName($firstName);
        }

        if ($lastName = $dto->getLastName()) {
            $builder->setLastName($lastName);
        }

        if ($password = $dto->getPassword()) {
            $builder->setPassword($password);
        }

        if ($group = $dto->getGroup()) {
            $builder->setGroup($group);
        }

        if ($phone = $dto->getPhone()) {
            $builder->setPhone(Phone::fromString($phone));
        }

        return $builder;
    }

    public function build(): User
    {
        if (!$this->email && $this->phone) {
            $this->email = $this->phone->getRaw() . '@example.com';
        } 
        if (!$this->email) {
            throw new \InvalidArgumentException("Email обязателен");
        }
        $group = $this->group ?? $this->userService->getDefaultUserGroups();
        if (!$this->password) {
            $this->password = $this->userService->generatePassword($group);
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
            'GROUP_ID' =>  $group,
        ]);
    }
}
