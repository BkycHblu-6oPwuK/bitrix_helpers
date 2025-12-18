# User, Repository, Service

Документация по работе с сущностью пользователя, репозиторием и сервисом.

## User Entity

Основной класс пользователя, реализующий `UserEntityContract`.

### Создание экземпляра

```php
use Beeralex\User\User;

$user = new User([
    'ID' => 1,
    'NAME' => 'Иван',
    'LAST_NAME' => 'Петров',
    'EMAIL' => 'ivan@example.com',
    'PERSONAL_PHONE' => '+79991234567',
]);
```

### Основные методы

#### Идентификация

```php
$user->getId(): ?int              // ID пользователя или null
$user->isAuthorized(): bool       // Авторизован ли текущий пользователь
$user->isAdmin(): bool            // Является ли администратором
```

#### Персональные данные

```php
$user->getName(): string          // Имя
$user->getLastName(): string      // Фамилия
$user->getPatronymic(): string    // Отчество
$user->getFullName(): string      // Полное имя (Имя Фамилия)
$user->getBirthday(): ?Date       // Дата рождения
```

#### Контактные данные

```php
$user->getEmail(): string         // Email
$user->getPhone(): ?Phone         // Объект Phone или null
$user->getPhoneAsString(): string // Телефон в формате E164
```

#### Безопасность

```php
$user->getPassword(): string      // Хеш пароля
$user->getCheckword(): string     // Checkword для восстановления пароля
```

#### Дополнительные данные

```php
$user->getFields(): array         // Все поля пользователя
$user->getUserGroup(): array      // Группы пользователя
```

### Пример использования

```php
$userRepo = service(UserRepositoryContract::class);
$user = $userRepo->getById(1);

if ($user) {
    echo "Имя: {$user->getFullName()}\n";
    echo "Email: {$user->getEmail()}\n";
    
    if ($phone = $user->getPhone()) {
        echo "Телефон: {$phone->formatInternational()}\n";
    }
    
    if ($birthday = $user->getBirthday()) {
        echo "Дата рождения: {$birthday->format('d.m.Y')}\n";
    }
}
```

## UserRepository

Репозиторий для работы с пользователями, реализующий `UserRepositoryContract`.

### Внедрение зависимостей

```php
use Beeralex\User\Contracts\UserRepositoryContract;

$userRepo = service(UserRepositoryContract::class);
```

### Методы получения

#### `getCurrentUser(bool $refresh = false): UserEntityContract`

Получает текущего авторизованного пользователя.

```php
$currentUser = $userRepo->getCurrentUser();

if ($currentUser->isAuthorized()) {
    echo "Привет, {$currentUser->getName()}!";
} else {
    echo "Вы не авторизованы";
}

// Принудительное обновление из БД
$currentUser = $userRepo->getCurrentUser(refresh: true);
```

#### `getById(int $userId, array $select = []): ?UserEntityContract`

Получает пользователя по ID.

```php
$user = $userRepo->getById(123);

if ($user) {
    echo $user->getEmail();
}

// С выборкой определенных полей
$user = $userRepo->getById(123, [
    'ID', 'NAME', 'EMAIL', 'PERSONAL_PHONE'
]);
```

#### `getByEmail(string $email, array $select = []): ?UserEntityContract`

Получает пользователя по email.

```php
$user = $userRepo->getByEmail('ivan@example.com');

if ($user) {
    echo "User ID: {$user->getId()}";
}
```

#### `getByPhone(Phone $phone, array $select = []): ?UserEntityContract`

Получает пользователя по номеру телефона.

```php
use Beeralex\User\Phone;

$phone = Phone::fromString('+79991234567');
$user = $userRepo->getByPhone($phone);

if ($user) {
    echo "Найден: {$user->getName()}";
}
```

### Методы изменения

#### `add(array|object $data): int`

Добавляет нового пользователя.

```php
$userId = $userRepo->add([
    'NAME' => 'Иван',
    'LAST_NAME' => 'Петров',
    'EMAIL' => 'ivan@example.com',
    'PASSWORD' => 'password123',
    'ACTIVE' => 'Y',
    'GROUP_ID' => [2], // Группы пользователя
]);

echo "Создан пользователь с ID: {$userId}";
```

**С объектом User:**

```php
$user = new User([
    'NAME' => 'Петр',
    'EMAIL' => 'petr@example.com',
]);

$userId = $userRepo->add($user);
```

#### `addByUser(User $user): int`

Добавляет пользователя из объекта User.

```php
$user = new User([
    'NAME' => 'Мария',
    'EMAIL' => 'maria@example.com',
    'PASSWORD' => 'secret123',
]);

$userId = $userRepo->addByUser($user);
echo $user->getId(); // ID установлен автоматически
```

#### `update(int $userId, array|object $data): void`

Обновляет данные пользователя.

```php
$userRepo->update(123, [
    'NAME' => 'Новое имя',
    'LAST_NAME' => 'Новая фамилия',
    'PERSONAL_PHONE' => '+79999999999',
]);
```

**С объектом User:**

```php
$user = $userRepo->getById(123);
// Изменяем поля через setters (если они есть) или напрямую
$userRepo->update(123, $user);
```

#### `save(array|object $data): int`

Сохраняет пользователя (добавляет если нет ID, обновляет если есть).

```php
// Новый пользователь (без ID)
$user = new User(['NAME' => 'Иван', 'EMAIL' => 'ivan@example.com']);
$userId = $userRepo->save($user); // Добавит

// Существующий пользователь (с ID)
$user = $userRepo->getById(123);
$userId = $userRepo->save($user); // Обновит
```

#### `delete(int $id): void`

Удаляет пользователя.

```php
try {
    $userRepo->delete(123);
    echo "Пользователь удален";
} catch (\RuntimeException $e) {
    echo "Ошибка: {$e->getMessage()}";
}
```

### Примеры использования

#### Поиск пользователя

```php
// По email
$user = $userRepo->getByEmail('test@example.com');

// По телефону
$phone = Phone::fromString('+79991234567');
$user = $userRepo->getByPhone($phone);

// По ID
$user = $userRepo->getById(123);

if (!$user) {
    echo "Пользователь не найден";
}
```

#### Создание нового пользователя

```php
try {
    $userId = $userRepo->add([
        'NAME' => 'Александр',
        'LAST_NAME' => 'Сидоров',
        'EMAIL' => 'alex@example.com',
        'PASSWORD' => 'securePassword123',
        'PERSONAL_PHONE' => '+79991234567',
        'PERSONAL_BIRTHDAY' => '01.01.1990',
        'GROUP_ID' => [2], // Зарегистрированные пользователи
    ]);
    
    echo "Создан пользователь ID: {$userId}";
} catch (\RuntimeException $e) {
    echo "Ошибка создания: {$e->getMessage()}";
}
```

#### Обновление профиля

```php
$user = $userRepo->getCurrentUser();

if ($user->isAuthorized()) {
    $userRepo->update($user->getId(), [
        'NAME' => $_POST['name'],
        'LAST_NAME' => $_POST['lastName'],
        'PERSONAL_PHONE' => $_POST['phone'],
    ]);
    
    // Обновляем кеш текущего пользователя
    $user = $userRepo->getCurrentUser(refresh: true);
}
```

## UserService

Сервис для работы с бизнес-логикой пользователей.

### Внедрение зависимостей

```php
use Beeralex\User\UserService;

$userService = service(UserService::class);
```

### Методы

#### `changePassword(User $user, string $password): Result`

Меняет пароль пользователя с валидацией политики безопасности.

```php
$user = $userRepo->getCurrentUser();

$result = $userService->changePassword($user, 'newSecurePassword123');

if ($result->isSuccess()) {
    echo "Пароль изменен успешно";
} else {
    foreach ($result->getErrorMessages() as $error) {
        echo "Ошибка: {$error}\n";
    }
}
```

#### `changePasswordByOldPassword(User $user, string $newPassword, string $oldPassword): Result`

Меняет пароль с проверкой старого пароля.

```php
$user = $userRepo->getCurrentUser();

$result = $userService->changePasswordByOldPassword(
    user: $user,
    newPassword: 'newPassword123',
    oldPassword: 'oldPassword123'
);

if (!$result->isSuccess()) {
    echo "Старый пароль неверен";
}
```

#### `restorePassword(string $email): Result`

Отправляет письмо с инструкцией по восстановлению пароля.

```php
$result = $userService->restorePassword('user@example.com');

if ($result->isSuccess()) {
    echo "Письмо отправлено на {$email}";
} else {
    echo "Пользователь с таким email не найден";
}
```

#### `changePasswordByCheckword(string $email, string $password, string $checkword): Result`

Устанавливает новый пароль по checkword из письма восстановления.

```php
$result = $userService->changePasswordByCheckword(
    email: 'user@example.com',
    password: 'newPassword123',
    checkword: $checkwordFromEmail
);

if ($result->isSuccess()) {
    echo "Пароль изменен, пользователь авторизован";
}
```

#### `updateProfile(User $user, array $fields): Result`

Обновляет профиль пользователя с использованием транзакций.

```php
$user = $userRepo->getCurrentUser();

$result = $userService->updateProfile($user, [
    'NAME' => 'Новое имя',
    'LAST_NAME' => 'Новая фамилия',
    'PERSONAL_PHONE' => '+79991234567',
    'PERSONAL_BIRTHDAY' => '01.01.1990',
    'PERSONAL_PHOTO' => $_FILES['photo'],
]);

if ($result->isSuccess()) {
    echo "Профиль обновлен";
}
```

### Примеры использования

#### Форма смены пароля

```php
if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
    $user = $userRepo->getCurrentUser();
    
    $result = $userService->changePasswordByOldPassword(
        user: $user,
        newPassword: $_POST['new_password'],
        oldPassword: $_POST['old_password']
    );
    
    if ($result->isSuccess()) {
        ShowMessage(['TYPE' => 'OK', 'MESSAGE' => 'Пароль изменен']);
    } else {
        foreach ($result->getErrors() as $error) {
            ShowError($error->getMessage());
        }
    }
}
```

#### Восстановление пароля (двухэтапное)

**Шаг 1: Запрос восстановления**

```php
// forgot-password.php
if ($_POST['email']) {
    $result = $userService->restorePassword($_POST['email']);
    
    if ($result->isSuccess()) {
        echo "Инструкция отправлена на ваш email";
    } else {
        echo "Пользователь не найден";
    }
}
```

**Шаг 2: Установка нового пароля**

```php
// reset-password.php?checkword=abc123&email=user@example.com
if ($_POST['password']) {
    $result = $userService->changePasswordByCheckword(
        email: $_GET['email'],
        password: $_POST['password'],
        checkword: $_GET['checkword']
    );
    
    if ($result->isSuccess()) {
        // Пользователь автоматически авторизован
        LocalRedirect('/personal/');
    } else {
        echo "Ссылка недействительна";
    }
}
```

#### Компонент обновления профиля

```php
class ProfileComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        $userRepo = service(UserRepositoryContract::class);
        $userService = service(UserService::class);
        
        $this->arResult['USER'] = $userRepo->getCurrentUser();
        
        if ($this->request->isPost() && check_bitrix_sessid()) {
            $result = $userService->updateProfile(
                $this->arResult['USER'],
                [
                    'NAME' => $this->request->getPost('name'),
                    'LAST_NAME' => $this->request->getPost('lastName'),
                    'PERSONAL_PHONE' => $this->request->getPost('phone'),
                    'PERSONAL_BIRTHDAY' => $this->request->getPost('birthday'),
                ]
            );
            
            if ($result->isSuccess()) {
                $this->arResult['SUCCESS'] = true;
                // Обновляем данные пользователя
                $this->arResult['USER'] = $userRepo->getCurrentUser(true);
            } else {
                $this->arResult['ERRORS'] = $result->getErrorMessages();
            }
        }
        
        $this->includeComponentTemplate();
    }
}
```

## UserFactory

Фабрика для создания экземпляров User.

```php
use Beeralex\User\Contracts\UserFactoryContract;

$factory = service(UserFactoryContract::class);
$user = $factory->create([
    'ID' => 1,
    'NAME' => 'Иван',
    'EMAIL' => 'ivan@example.com',
]);
```

## UserBuilder

Билдер для пошагового создания пользователя.

### Использование

```php
use Beeralex\User\Contracts\UserBuilderContract;
use Beeralex\User\Phone;

$builder = service(UserBuilderContract::class);

$user = $builder
    ->setEmail('user@example.com')
    ->setName('Иван')
    ->setLastName('Петров')
    ->setPassword('password123')
    ->setPhone(Phone::fromString('+79991234567'))
    ->setGroup([2, 5])
    ->build();

// Сохраняем
$userRepo = service(UserRepositoryContract::class);
$userId = $userRepo->addByUser($user);
```

### Создание из AuthCredentialsDto

```php
use Beeralex\User\UserBuilder;
use Beeralex\User\Auth\AuthCredentialsDto;

$credentials = new AuthCredentialsDto(
    type: 'email',
    email: 'user@example.com',
    password: 'password123',
    firstName: 'Иван',
    lastName: 'Петров'
);

$builder = UserBuilder::fromDto($credentials);
$user = $builder->build();
```

## Навигация

- [← Быстрый старт](getting-started.md)
- [Система аутентификации →](authentication.md)
