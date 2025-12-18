# Модуль пользователей (beeralex.user)

Модуль для управления пользователями, аутентификацией и JWT токенами в Bitrix Framework.

## Возможности

- 🔐 **Многостратегийная аутентификация** — Email, телефон, социальные сети
- 🎫 **JWT токены** — Access/Refresh токены с управлением сессиями
- 📱 **Телефоны** — Работа с международными номерами через
- 🌐 **Социальные сети** — Интеграция с Bitrix Social Services (Google, Yandex, VK, и др.)
- 🔌 **Расширяемость** — Кастомные аутентификаторы, валидаторы, middleware
- 📦 **DI Container** — Все сервисы доступны через внедрение зависимостей

## Требования

- PHP 8.1+
- Bitrix Framework 22.0+
- Composer

## Быстрый старт

### Регистрация пользователя

```php
use Beeralex\User\Auth\AuthService;
use Beeralex\User\Auth\AuthCredentialsDto;

$authService = service(AuthService::class);

$credentials = new AuthCredentialsDto(
    type: 'email',
    email: 'user@example.com',
    password: 'password123',
    firstName: 'Иван',
    lastName: 'Иванов'
);

$result = $authService->register($credentials);

if ($result->isSuccess()) {
    $data = $result->getData();
    echo "Пользователь зарегистрирован. ID: {$data['userId']}";
    
    // JWT токены (если включены) лучше хранить в httpOnly cookies
    // См. пример в AuthController
}
```

### Вход пользователя

```php
$credentials = new AuthCredentialsDto(
    type: 'email',
    email: 'user@example.com',
    password: 'password123'
);

$result = $authService->login($credentials);

if ($result->isSuccess()) {
    echo "Авторизация успешна";
}
```

### Работа с репозиторием

```php
use Beeralex\User\Repository\UserRepositoryContract;

$userRepo = service(UserRepositoryContract::class);

// Получить по ID
$user = $userRepo->getById(123);

// Получить по email
$user = $userRepo->getByEmail('user@example.com');

// Получить текущего пользователя
$currentUser = $userRepo->getCurrentUser();

if ($user) {
    echo $user->getFullName();
    echo $user->getEmail();
    echo $user->getPhone()->formatInternational();
}
```

### REST API контроллер

```php
use Beeralex\Core\Http\Controllers\ApiController;
use Beeralex\User\Auth\AuthService;

class AuthController extends ApiController
{
    public function loginAction()
    {
        $data = $this->getJsonPayload();
        
        $authService = service(AuthService::class);
        
        $credentials = new AuthCredentialsDto(
            type: $data['type'],
            email: $data['email'] ?? null,
            password: $data['password'] ?? null
        );
        
        $result = $authService->login($credentials);
        
        if ($result->isSuccess()) {
            return [
                'status' => 'success',
                'data' => $result->getData()
            ];
        }
        
        return [
            'status' => 'error',
            'errors' => $result->getErrorMessages()
        ];
    }
}
```

## Архитектура

```
┌─────────────────────────────────────────┐
│            AuthService                  │ ← High-level API
│  (login, register, refreshTokens)       │
└────────────┬────────────────────────────┘
             │
             ├─ AuthManager ──────────────────┐
             │  (координатор authenticators)  │
             │                                 │
             ├─ EmailAuthenticator            │
             ├─ PhoneAuthenticator            │
             └─ SocialAuthenticators[]        │
                                              │
             ┌─ JwtTokenManager               │
             │  (генерация и валидация)       │
             └────────────────────────────────┘

┌─────────────────────────────────────────┐
│         UserRepository                  │ ← Data access
│  (getById, getByEmail, CRUD)            │
└────────────┬────────────────────────────┘
             │
             ├─ UserFactory
             ├─ UserBuilder
             └─ User (entity)

┌─────────────────────────────────────────┐
│          UserService                    │ ← Business logic
│  (changePassword, updateProfile)        │
└─────────────────────────────────────────┘
```

## Конфигурация

### JWT токены

Настройка выполняется через административную панель:

**Настройки → Настройки модулей → Модуль пользователей (beeralex.user)**

- ✅ Включить JWT авторизацию
- Секретный ключ (256 бит)
- Алгоритм: HS256, HS384, HS512
- Время жизни access/refresh токенов

### Социальные сети

Настраиваются через стандартный интерфейс Bitrix:

**Настройки → Интеграция с соцсетями → Авторизация через соцсети**

Активные соцсети автоматически доступны в модуле.

### SMS провайдер

**Настройки → Настройки продукта → SMS-провайдеры**

Модуль использует настроенный в Bitrix SMS-провайдер.

## Основные компоненты

### AuthService
Высокоуровневый API для аутентификации:
- `login()` — Вход пользователя
- `register()` — Регистрация
- `refreshTokens()` — Обновление JWT токенов
- `logout()` — Выход

### AuthManager
Координатор аутентификаторов:
- `authenticate()` — Делегирует аутентификацию нужному authenticator
- `register()` — Регистрация через authenticator
- `getAvailable()` — Список доступных методов

### UserRepository
Репозиторий для работы с пользователями:
- `getById()`, `getByEmail()`, `getByPhone()`
- `getCurrentUser()` — Текущий пользователь
- `add()`, `update()`, `save()`, `delete()`

### UserService
Бизнес-логика:
- `changePassword()`, `restorePassword()`
- `updateProfile()`

### JwtTokenManager
Управление JWT:
- `generateTokenPair()` — Генерация access + refresh
- `validateAccessToken()`, `validateRefreshToken()`
- `refreshTokens()` — Обновление токенов
- `revokeRefreshToken()` — Отзыв токена

### Phone
Value object для телефонов:
- `fromString()` — Создание из строки
- `formatE164()`, `formatInternational()`, `formatNational()`
- `isValid()`, `getCountryCode()`, `getRegionCode()`

## Примеры использования

### Аутентификация по телефону

```php
// Шаг 1: Отправка SMS кода
$credentials = new AuthCredentialsDto(
    type: 'phone',
    phone: '+79991234567'
);

$result = $authService->login($credentials);
// Код отправлен на телефон

// Шаг 2: Проверка кода
$credentials = new AuthCredentialsDto(
    type: 'phone',
    phone: '+79991234567',
    codeVerify: '1234'
);

$result = $authService->login($credentials);
// Пользователь авторизован
```

### Обновление JWT токенов

```php
$jwtManager = service(JwtTokenManager::class);

$result = $jwtManager->refreshTokens($oldRefreshToken);

if ($result->isSuccess()) {
    $newAccessToken = $result->getData()['accessToken'];
    $newRefreshToken = $result->getData()['refreshToken'];
}
```

### Middleware для защищенных API

```php
class JwtMiddleware
{
    public function handle(): ?int
    {
        $jwtManager = service(JwtTokenManager::class);
        $token = $this->extractToken();
        
        $result = $jwtManager->validateAccessToken($token);
        
        if (!$result->isSuccess()) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized']);
            die();
        }
        
        return $result->getData()['userId'];
    }
    
    protected function extractToken(): ?string
    {
        // Сначала Authorization header (мобильные приложения)
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }
        
        // Затем httpOnly cookie (веб-приложения)
        return $_COOKIE['access'] ?? null;
    }
}
```

**Преимущества httpOnly cookies:**
- 🔒 Защита от XSS атак
- 🚀 Автоматическая отправка
- 🛡️ Защита от CSRF

## Расширение

### Кастомный аутентификатор

```php
use Beeralex\User\Auth\Contracts\AuthenticatorContract;

class BiometricAuthenticator implements AuthenticatorContract
{
    public function getKey(): string { return 'biometric'; }
    public function getTitle(): string { return 'Биометрия'; }
    
    public function authenticate(AuthCredentialsDto $credentials): Result
    {
        // Ваша логика биометрической аутентификации
    }
}
```

Регистрация в `/local/.settings_extra.php`:

```php
return [
    'beeralex.user' => [
        'value' => [
            'container' => [
                'AuthManager' => [
                    'constructorParams' => static function() {
                        $authenticators = [
                            service(EmailAuthenticator::class),
                            service(BiometricAuthenticator::class),
                        ];
                        // ...
                    }
                ]
            ]
        ]
    ]
];
```

## Документация

Полная документация доступна в папке [`/docs/`](docs/):

- **[Начало работы](docs/getting-started.md)** — Установка, настройка, первые шаги
- **[Сущность User](docs/user-entity.md)** — User, UserRepository, UserService, Factory, Builder
- **[Аутентификация](docs/authentication.md)** — AuthManager, AuthService, Authenticators, Validators
- **[JWT токены](docs/jwt-tokens.md)** — JwtTokenManager, сессии, middleware
- **[Работа с телефонами](docs/phone.md)** — Phone класс, валидация, форматирование
- **[Социальная аутентификация](docs/social-auth.md)** — Google, Yandex, VK, custom провайдеры
- **[Расширение модуля](docs/extending.md)** — Кастомные authenticators, validators, DI override

## Лицензия

Proprietary

## Автор

Beeralex
