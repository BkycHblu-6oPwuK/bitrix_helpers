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
    /** @var Phone */
    private $phone;

    /**
     * @param string $email
     *
     * @return UserBuilder
     */
    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    /**
     * @param string $name
     *
     * @return UserBuilder
     */
    public function setName(string $name): self
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @param string $lastName
     *
     * @return UserBuilder
     */
    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    /**
     * @param string $password
     *
     * @return UserBuilder
     */
    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    /**
     * @param mixed $phone
     *
     * @return UserBuilder
     */
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


    public function build(): User
    {
        if (!$this->email) {
            $this->email = $this->phone->getNumber() . '@example.com';
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
            'PERSONAL_PHONE'   => $this->phone->getNumber(),
            'CONFIRM_PASSWORD' => $this->password,
            'ACTIVE' => 'Y',
            'LID' => Context::getCurrent()->getSite(),
            'GROUP_ID' => $this->group ?? UserHelper::getDefaultUserGroups(),
        ]);
    }
}
