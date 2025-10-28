<?php
namespace Beeralex\User\Profile;

use Beeralex\User\Enum\Gender;

class PersonalDTO 
{
    public string $name = '';
    public string $lastName = '';
    public string $email = '';
    public string $phone = '';
    public string $birthday = '';
    public ?string $photo = null;
    public ?string $gender = null;
    public array $genderMap = [];
    /**
     * @var NotificationDTO[] $notifications
     */
    public array $notifications = [];

    public function __construct()
    {
        $this->genderMap = [
            Gender::WOMAN->value => Gender::PROFILE_WOMAN->value,
            Gender::MAN->value => Gender::PROFILE_MAN->value
        ];
    }

    public function setGender(?Gender $gender)
    {
        if(!$gender) return;
        $this->gender = match($gender){
            $gender::MAN => Gender::MAN->value,
            $gender::WOMAN => Gender::WOMAN->value,
            $gender::PROFILE_WOMAN => Gender::WOMAN->value,
            $gender::PROFILE_MAN => Gender::MAN->value,
            default => null,
        };
    }
}