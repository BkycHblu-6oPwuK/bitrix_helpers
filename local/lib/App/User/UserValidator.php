<?php

namespace App\User;

use Bitrix\Main\Config\Option;
use Bitrix\Main\Type\DateTime;
use Itb\Core\Config;
use App\User\Enum\Gender;
use App\User\UserRepository;
use App\User\Phone\Phone;

class UserValidator
{
    /** @var array обязательные поля пользователя для вызова CUser::Add */
    private static $requiredFields = ['EMAIL', 'PASSWORD', 'LOGIN', 'PERSONAL_PHONE'];


    private $errors = [];


    /**
     * Получает ошибки валидации имени пользователя
     *
     * @param $name
     *
     * @return array
     */
    private function getNameValidationErrors($name): array
    {
        $errors = [];
        if (empty($name)) {
            $errors[] = 'Имя должно быть заполнено';
        }

        return $errors;
    }


    /**
     * Получает ошибки валидации email'а
     *
     * @param $email
     *
     * @return array
     */
    private function getEmailValidationErrors($email): array
    {
        $errors = [];

        $emailRequired = (Option::get('main', 'new_user_email_required', 'Y') !== 'N');
        if ($emailRequired && !$email) {
            $errors[] = 'Email должен быть заполнен';
        }

        if ($email != '' && !check_email($email, true)) {
            $errors[] = 'Email введен неверно';
        }

        // проверяем что нет пользователей с данным емаилом
        if (Option::get('main', 'new_user_email_uniq_check', 'N') === 'Y' && $email != '') {
            $user = (new UserRepository)->getByEmail($email);
            if ($user && $user->getId() != User::current()->getId()) {
                $errors[] = 'Пользователь с таким email уже зарегистрирован';
            }
        }
        return $errors;
    }


    /**
     * Получает ошибки валидации пароля
     *
     * @param       $password
     * @param array $groupIds ID групп, в которые должен быть добавлен пользователь. Если не передан,
     *                        то для авторизованного пользователя берутся его группы.
     *
     * @return array
     */
    private function getPasswordValidationErrors($password, $groupIds = []): array
    {
        if (!empty($groupIds)) {
            $policy = \CUser::GetGroupPolicy($groupIds);
        } elseif (User::current()->isAuthorized()) {
            $policy = \CUser::GetGroupPolicy(User::current()->getId());
        } else {
            $policy = \CUser::GetGroupPolicy([]);
        }

        return \CUser::CheckPasswordAgainstPolicy($password, $policy);
    }

    private function getPhoneValidationErrors(?Phone $phone, ?string $email): array
    {
        $errors = [];
        if (!$phone) {
            if (!$email) {
                $errors[] = 'Номер телефона обязателен для заполнения';
            }
            return $errors;
        }

        if (mb_strlen($phone->getNumber()) < 11) {
            $errors[] = 'Неверно введен номер телефона';
        }
        $user = (new UserRepository())->getByPhone($phone);
        if ($user && $user->getId() != User::current()->getId()) {
            $errors[] = 'Пользователь с таким номером телефона уже зарегистрирован';
        }
        return $errors;
    }

    private function getGenderValidationErrors(mixed &$gender, bool $isRequired)
    {
        $errors = [];
        if (!$gender) {
            if ($isRequired) {
                $errors[] = 'Не выбран пол';
            }
        }
        if (empty($errors)) {
            $gender = Gender::getProfileGenderBySite($gender ?? '');
        }
        return $errors;
    }

    private function getBirthdayValidationErrors(?string &$birthday, bool $isRequired)
    {
        $errors = [];
        try {
            if (!$birthday) {
                if ($isRequired) {
                    $errors[] = 'Поле обязательно для заполнения';
                }
                return;
            }
            $date = DateTime::createFromPhp(\DateTime::createFromFormat(Config::getInstance()->dateFormatSite, $birthday));
            if (!$date) {
                throw new \Exception();
            }
            $birthday = $date;
        } catch (\Exception) {
            $errors[] = 'Введена неверная дата';
        }
        return $errors;
    }


    /**
     * Валидирует поля личных данных пользователя и возвращает ошибки валидации
     *
     * @param array $fields        [code => value] коды как при вызове метода CUser::GetList()
     * @param bool  $checkRequired нужно ли проверять переданные поля на обязательность (для вызова CUser::Add)
     *
     * @return bool isValid
     */
    public function validateFields(array $fields, bool $checkRequired = false): bool
    {
        foreach ($fields as $key => $field) {
            if (empty($field)) unset($fields[$key]);
        }

        $this->errors = [];

        if ($fields['NAME']) {
            if ($nameErrors = $this->getNameValidationErrors($fields['NAME'])) {
                $this->errors['name'] = $nameErrors;
            }
        }

        if ($fields['EMAIL']) {
            if ($emailErrors = $this->getEmailValidationErrors($fields['EMAIL'])) {
                $this->errors['email'] = $emailErrors;
            }
        }

        if ($fields['PASSWORD']) {
            if ($passwordErrors = $this->getPasswordValidationErrors($fields['PASSWORD'])) {
                $this->errors['password'] = $passwordErrors;
            }
        }

        $phone = $fields['phone'];
        if (!$phone) {
            $phoneNumber = $fields['PERSONAL_PHONE'] ?? $fields['PHONE_NUMBER'] ?? null;
            if ($phoneNumber) {
                $phone = new Phone($phoneNumber);
            }
        }

        if ($phone) {
            if ($phoneErrors = $this->getPhoneValidationErrors($phone, $fields['EMAIL'])) {
                $this->errors['phone'] = $phoneErrors;
            }
        }

        // отмечаем все обязательные поля
        if ($checkRequired) {
            $missedRequiredFields = array_diff(static::$requiredFields, array_keys($fields));
            if (!empty($missedRequiredFields)) {
                foreach ($missedRequiredFields as &$field) {
                    if ($field === 'PERSONAL_PHONE') $field = 'phone';
                    $field = mb_strtolower($field);
                }
                $this->errors = array_merge(
                    $this->errors,
                    array_fill_keys($missedRequiredFields, ['Поле обязательно для заполнения'])
                );
            }
        }
        return !$this->hasErrors();
    }


    /**
     * @param User $user
     *
     * @param bool $checkRequired
     *
     * @return bool
     */
    public function validateUser(User $user, bool $checkRequired = false): bool
    {
        return $this->validateFields($user->getFields(), $checkRequired);
    }


    /**
     * Получает ошибки валидации имени пользователя
     *
     * @param $name
     *
     * @return bool
     */
    public function validateName($name): bool
    {
        $this->errors = $this->getNameValidationErrors($name);
        return !$this->hasErrors();
    }

    public function validatePhone(?Phone $phone, ?string $email = null): bool
    {
        $this->errors = $this->getPhoneValidationErrors($phone, $email);
        return !$this->hasErrors();
    }

    /**
     * Получает ошибки валидации email'а
     *
     * @param $email
     *
     * @return bool
     */
    public function validateEmail($email): bool
    {
        $this->errors = $this->getEmailValidationErrors($email);
        return !$this->hasErrors();
    }

    /**
     * Получает ошибки валидации пароля
     *
     * @param       $password
     * @param array $groupIds ID групп, в которые должен быть добавлен пользователь. Если не передан,
     *                        то для авторизованного пользователя берутся его группы.
     *
     * @return bool
     */
    public function validatePassword($password, array $groupIds = []): bool
    {
        $this->errors = $this->getPasswordValidationErrors($password, $groupIds);
        return !$this->hasErrors();
    }

    /**
     * Так же подменяет значение сайтом значением для базы
     */
    public function validateGender(mixed &$gender, bool $isRequired = false)
    {
        $this->errors = $this->getGenderValidationErrors($gender, $isRequired);
        return !$this->hasErrors();
    }

    /**
     * Так же подменяет строку даты на объект DateTime bitrix
     */
    public function validateBirthday(mixed &$birthday, bool $isRequired = false)
    {
        $this->errors = $this->getBirthdayValidationErrors($birthday, $isRequired);
        return !$this->hasErrors();
    }

    /**
     * @return bool есть ли ошибки после вызова validate()
     */
    public function hasErrors(): bool
    {
        return !empty($this->getErrors());
    }

    /**
     * После вызова методов валидации одного поля (например validateName) вернет массив ошибок.
     * После вызова метода validate вернет массив вида [field => arrayOfError]
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }
}
