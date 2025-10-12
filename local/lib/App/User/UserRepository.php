<?php

namespace App\User;

use App\User\Exceptions\ValidationException;
use App\User\Phone\Phone;
use App\User\Phone\PhoneFormatter;
use Beeralex\Core\Repository\AbstractRepository;
use Bitrix\Main\UserTable;

class UserRepository extends AbstractRepository implements UserRepositoryContract
{
    protected PhoneFormatter $phoneFormatter;

    public function __construct()
    {
        $this->phoneFormatter = new PhoneFormatter();
        parent::__construct(UserTable::class);
    }

    /**
     * Получает пользователя по заданному емаилу
     */
    public function getByEmail(string $email, array $select = []): ?User
    {
        $fields = \CUser::GetList(
            arFilter: ['=EMAIL' => $email],
            arParams: array_merge(static::FIELD_SELECT_DEFAULT, $select)
        )->Fetch();

        return $fields ? new User($fields) : null;
    }

    /**
     * Получает пользователя по заданному номеру телефона
     */
    public function getByPhone(Phone $phone, array $select = []): ?User
    {
        $fields = \CUser::GetList(
            arFilter: ['PERSONAL_PHONE' => $phone->getNumber()],
            arParams: array_merge(static::FIELD_SELECT_DEFAULT, $select)
        )->Fetch();

        return $fields ? new User($fields) : null;
    }

    /**
     * Получает пользователя по ID
     */
    public function getById(int $userId, array $select = []): ?User
    {
        $fields = \CUser::GetList(
            arFilter: ['ID_EQUAL_EXACT' => $userId],
            arParams: array_merge(static::FIELD_SELECT_DEFAULT, $select)
        )->Fetch();

        return $fields ? new User($fields) : null;
    }

    /**
     * Добавляет нового пользователя
     *
     * @throws RuntimeException
     * @throws ValidationException
     */
    public function addByUser(User $user): int
    {
        $fields = $this->modifyFields($user->getFields());
        $cuser = new \CUser();

        $id = $cuser->Add($fields);
        if (!$id) {
            throw new \RuntimeException($cuser->LAST_ERROR);
        }

        $user->setId((int)$id);
        return (int)$id;
    }

    /**
     * Обновляет данные пользователя
     *
     * @throws RuntimeException
     * @throws ValidationException
     */
    public function update(int $userId, array|object $data): void
    {
        $fields = $data instanceof User ? $data->getFields() : $data;
        $fields = $this->modifyFields($fields);

        $user = new \CUser();
        if (!$user->Update($userId, $fields)) {
            throw new \RuntimeException($user->LAST_ERROR);
        }
    }

    /**
     * Удаляет пользователя
     *
     * @throws RuntimeException
     */
    public function delete(int $id): void
    {
        $result = \CUser::Delete($id);
        if (!$result) {
            global $APPLICATION;
            $error = $APPLICATION->GetException();
            $message = $error ? $error->GetString() : "Cannot delete user with ID {$id}";
            throw new \RuntimeException($message);
        }
    }

    /**
     * Добавляет пользователя (реализация интерфейса)
     */
    public function add(array|object $data): int
    {
        if ($data instanceof User) {
            return $this->addByUser($data);
        }

        $cuser = new \CUser();
        $fields = $this->modifyFields($data);
        $id = $cuser->Add($fields);

        if (!$id) {
            throw new \RuntimeException($cuser->LAST_ERROR);
        }

        return (int)$id;
    }

    /**
     * Сохраняет (добавляет или обновляет) пользователя
     */
    public function save(array|object $data): int
    {
        $user = $data instanceof User ? $data : new User($data);

        if ($user->getId()) {
            $this->update($user->getId(), $user);
            return $user->getId();
        }

        return $this->addByUser($user);
    }

    /**
     * Возвращает одного пользователя по фильтру
     */
    public function one(array $filter, array $select = []): ?User
    {
        $fields = \CUser::GetList(
            arFilter: $filter,
            arParams: array_merge(static::FIELD_SELECT_DEFAULT, $select)
        )->Fetch();

        return $fields ? new User($fields) : null;
    }

    /**
     * Возвращает всех пользователей по фильтру
     * @return User[]
     */
    public function all(array $filter = [], array $select = [], array $order = []): array
    {
        $result = [];
        $res = \CUser::GetList(
            key($order) ?: 'ID',
            current($order) ?: 'ASC',
            $filter,
            array_merge(static::FIELD_SELECT_DEFAULT, $select)
        );

        while ($fields = $res->Fetch()) {
            $result[] = new User($fields);
        }

        return $result;
    }

    /**
     * Приводит свойства к нужному виду перед записью в БД
     */
    protected function modifyFields(array $fields): array
    {
        $phoneNumber = $fields['PERSONAL_PHONE'] ?? $fields['PHONE_NUMBER'] ?? null;

        if (!empty($phoneNumber)) {
            $formatted = $this->phoneFormatter->formatForDb($phoneNumber);
            $fields['PERSONAL_PHONE'] = $formatted;
            $fields['PHONE_NUMBER'] = $formatted;
        }

        if (!empty($fields['EMAIL'])) {
            $fields['LOGIN'] = $fields['EMAIL'];
        }

        return $fields;
    }
}
