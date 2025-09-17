<?php

namespace Itb\User;

use Itb\User\Exceptions\ValidationException;
use Itb\User\Phone\Phone;
use Itb\User\Phone\PhoneFormatter;

class UserRepository
{
    /** @var array поля для выборки из CUser::GetList() */
    private $fieldsToSelect = [
        'FIELDS' => [
            'ID',
            'NAME',
            'LAST_NAME',
            'SECOND_NAME',
            'EMAIL',
            'PERSONAL_PHONE',
            'PERSONAL_BIRTHDAY',
            'PERSONAL_GENDER',
            'PASSWORD',
            'CHECKWORD',
        ],
        'SELECT' => [
            'UF_PHOTO'
        ]
    ];

    /**
     * @var PhoneFormatter
     */
    private $phoneFormatter;

    /**
     * @var \CUser|string $entity
     */
    protected readonly string $entity;

    public function __construct()
    {
        $this->phoneFormatter = new PhoneFormatter();
        $this->entity = \CUser::class;
    }


    /**
     * Получает пользователя по заданному емаилу
     *
     * @param string $email
     *
     * @return User|null
     */
    public function getByEmail(string $email): User|null
    {
        $fields = $this->entity::GetList(
            $by = [],
            $order = [],
            ['=EMAIL' => $email],
            $this->fieldsToSelect
        )->Fetch();
        return $fields ? new User($fields) : null;
    }

    /**
     * Получает пользователя по заданному номеру телефона
     *
     * @param Phone $phone
     *
     * @return User|null
     */
    public function getByPhone(Phone $phone): User|null
    {
        $fields = $this->entity::GetList(
            $by = [],
            $order = [],
            ['PERSONAL_PHONE' => $phone->getNumber()],
            $this->fieldsToSelect
        )->Fetch();
        return $fields ? new User($fields) : null;
    }

    /**
     * @param int $userId
     *
     * @return User|null
     */
    public function getById(int $userId): User|null
    {
        $fields = $this->entity::GetList(
            $by = [],
            $order = [],
            ['ID_EQUAL_EXACT' => $userId],
            $this->fieldsToSelect
        )->Fetch();
        return $fields ? new User($fields) : null;
    }

    /**
     * @param User $user
     *
     * @return int
     *
     * @throws \RuntimeException
     * @throws ValidationException
     */
    public function add(User $user): int
    {
        $validator = new UserValidator();
        if (!$validator->validateUser($user, true)) {
            throw new ValidationException('Invalid user ' . join(', ', $validator->getErrors()));
        }

        $cuser = new $this->entity;
        $fields = $this->modifyFields($user->getFields());
        if (!($id = $cuser->Add($fields))) {
            throw new \RuntimeException($cuser->LAST_ERROR);
        }

        $user->setId($id);

        return $id;
    }


    /**
     * @param int   $userId
     * @param array $fields
     *
     * @throws \RuntimeException
     * @throws ValidationException
     */
    public function update(int $userId, array $fields): void
    {
        $fields = $this->modifyFields($fields);

        $validator = new UserValidator();
        if (!($validator)->validateFields($fields)) {
            $errors = collect($validator->getErrors())
                ->map(function ($errors, $key) {
                    return $key . ': ' . implode(', ', $errors);
                })
                ->implode(', ');
            throw new ValidationException('Invalid user fields ' . $errors);
        }

        $user = new $this->entity;
        if (!($res = $user->Update($userId, $fields))) {
            throw new \RuntimeException($user->LAST_ERROR);
        }
    }


    /**
     * Приводит свойства к нужному виду
     *
     * @param array $fields
     *
     * @return array
     */
    private function modifyFields(array $fields): array
    {
        $phoneNumber = $fields['PERSONAL_PHONE'] ? $fields['PERSONAL_PHONE'] : $fields['PHONE_NUMBER'];
        if (!empty($phoneNumber)) {
            $fields['PERSONAL_PHONE'] = $this->phoneFormatter->formatForDb($phoneNumber);
            $fields['PHONE_NUMBER'] = $this->phoneFormatter->formatForDb($phoneNumber);
        }

        if (isset($fields['EMAIL'])) {
            $fields['LOGIN'] = $fields['EMAIL'];
        }

        return $fields;
    }
}
