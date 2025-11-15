<?php
declare(strict_types=1);
namespace Beeralex\User;

use Beeralex\Core\Helpers\FilesHelper;
use Beeralex\User\Exceptions\ValidationException;
use Beeralex\User\Phone;
use Beeralex\Core\Repository\AbstractRepository;
use Beeralex\User\Contracts\UserEntityContract;
use Beeralex\User\Contracts\UserFactoryContract;
use Beeralex\User\Contracts\UserRepositoryContract;
use Beeralex\User\UserTable;

class UserRepository extends AbstractRepository implements UserRepositoryContract
{
    public function __construct(protected readonly UserFactoryContract $factory)
    {
        parent::__construct(UserTable::class);
    }

    /**
     * Получает пользователя по заданному емаилу
     */
    public function getByEmail(string $email, array $select = []): ?UserEntityContract
    {
        $fields = FilesHelper::addPictireSrcInQuery($this->query(), 'PERSONAL_PHOTO')->setSelect(static::FIELD_SELECT_DEFAULT, $select)->where('EMAIL', $email)->enablePrivateFields()->fetch();
        return $fields ? $this->factory->create($fields) : null;
    }

    /**
     * Получает пользователя по заданному номеру телефона
     */
    public function getByPhone(Phone $phone, array $select = []): ?UserEntityContract
    {
        $fields = FilesHelper::addPictireSrcInQuery($this->query(), 'PERSONAL_PHOTO')->setSelect(static::FIELD_SELECT_DEFAULT, $select)->where('PHONE_AUTH.PHONE_NUMBER', $phone->formatE164())->enablePrivateFields()->fetch();
        return $fields ? $this->factory->create($fields) : null;
    }

    /**
     * Получает пользователя по ID
     */
    public function getById(int $userId, array $select = []): ?UserEntityContract
    {
        $fields = FilesHelper::addPictireSrcInQuery($this->query(), 'PERSONAL_PHOTO')->setSelect(static::FIELD_SELECT_DEFAULT, $select)->where('ID', $userId)->enablePrivateFields()->fetch();
        return $fields ? $this->factory->create($fields) : null;
    }

    /**
     * Получает текущего авторизованного пользователя
     */
    public function getCurrentUser(array $select = []): UserEntityContract
    {
        global $USER;
        static $currentUser;
        if ($currentUser === null) {
            if ($userId = $USER->GetID()) {
                $currentUser = $this->getById($userId, $select) ?? $this->factory->create([]);
            } else {
                $currentUser = $this->factory->create([]);
            }
        }
        
        return $currentUser;
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
    public function one(array $filter = [], array $select = []): ?UserEntityContract
    {
        $fields = FilesHelper::addPictireSrcInQuery($this->query(), 'PERSONAL_PHOTO')->setSelect(static::FIELD_SELECT_DEFAULT, $select)->setFilter($filter)->setLimit(1)->enablePrivateFields()->fetch();
        return $fields ? $this->factory->create($fields) : null;
    }

    /**
     * Возвращает всех пользователей по фильтру
     * @return User[]
     */
    public function all(array $filter = [], array $select = [], array $order = []): array
    {
        $result = [];
        $res = FilesHelper::addPictireSrcInQuery($this->query(), 'PERSONAL_PHOTO')->setFilter($filter)->setSelect(array_merge(static::FIELD_SELECT_DEFAULT, $select))->setOrder($order)->enablePrivateFields()->exec();
        while ($item = $res->fetch()) {
            $result[] = $this->factory->create($item);
        }

        return $result;
    }

    protected function modifyFields(array $fields): array
    {
        if (!empty($fields['EMAIL'])) {
            $fields['LOGIN'] = $fields['EMAIL'];
        }

        return $fields;
    }
}
