<?php

namespace Itb\User;

use Bitrix\Main\UserTable;
use Bitrix\Main\Type\Date;
use Itb\Enum\Gender;
use Itb\User\UserRepository;
use Itb\User\Phone\Phone;

class User
{
    /**
     * @var array
     */
    private $fields = [];
    /**
     * @var ?Phone
     */
    private $phone;


    public function __construct(array $fields)
    {
        $this->fields = $fields;
        $this->buildPhoto();
        $this->buildPhone();
    }

    private function buildPhoto()
    {
        if(!$this->fields['UF_PHOTO']) {
            $this->fields['UF_PHOTO'] = null;
            return;
        }
        $this->fields['UF_PHOTO'] = \CFile::GetPath($this->fields['UF_PHOTO']);
    }

    private function buildPhone(): void
    {
        if (isset($this->fields['phone']) || !$this->fields['PERSONAL_PHONE']) {
            return;
        }

        $this->phone = new Phone($this->fields['PERSONAL_PHONE']);
        $this->fields['phone'] = $this->phone;
    }


    /**
     * @return User текущий пользователь
     */
    public static function current(): self
    {
        static $user = null;

        if ($user === null) {
            global $USER;
            if ($userId = $USER->GetID()) {
                $user = (new UserRepository())->getById($userId);
            } else {
                $user = new self([]);
            }
        }

        return $user;
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
        return $this->fields['ID'];
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
        return $this->getPhone()?->getNumber() ?? '';
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

    public function getCheckword(): string
    {
        return $this->fields['CHECKWORD'] ?? '';
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

    public static function getFullNameByID(int|string $id): string 
    {
        $fullname = '';
        $user = UserTable::query()->setSelect(['NAME','LAST_NAME'])->where('ID',$id)->fetch();
        if(!empty($user['NAME'])){
            $fullname .= $user['NAME'] . ' ';
        }
        if(!empty($user['LAST_NAME'])){
            $fullname .= $user['LAST_NAME'];
        }
        return $fullname;
    }

    public function getPhoto(): ?string
    {
        return $this->fields['UF_PHOTO'];
    }

    public function getGender() : ?Gender
    {
        if($this->fields['PERSONAL_GENDER']){
            return match($this->fields['PERSONAL_GENDER']){
                Gender::PROFILE_MAN->value => Gender::MAN,
                Gender::PROFILE_WOMAN->value => Gender::WOMAN,
                default => null
            };
        }
        return null;
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
}
