# Быстрый старт

## Установка

### Требования

- PHP 8.1+
- Bitrix Framework 22.0+
- Composer
- Модуль `beeralex.core`

### Установка через Composer

```bash
composer require beeralex/beeralex.user
```

### Ручная установка

1. Разместите модуль в `/local/modules/beeralex.user/`
2. Установите через админку Bitrix
3. Модуль автоматически:
   - Зарегистрирует сервисы в DI
   - Создаст таблицы (user_sessions, user_phone_auth)
   - Настроит обработчики событий

## Базовая настройка

### Настройка JWT (опционально)

## Настройка JWT токенов

Настройка JWT выполняется через административную панель:

**Настройки → Настройки модулей → Модуль пользователей (beeralex.user)**

- ✅ **Включить JWT авторизацию** - активация JWT токенов
- **Секретный ключ** - криптографически стойкий ключ (256 бит)
- **Алгоритм** - HS256, HS384 или HS512
- **Время жизни access токена** - в секундах (по умолчанию 1200 = 20 минут)
- **Время жизни refresh токена** - в секундах (по умолчанию 2592000 = 30 дней)

⚠️ **Важно:** Используйте криптографически стойкий секретный ключ!

Генерация секретного ключа:
```bash
php -r "echo bin2hex(random_bytes(32));"
```

### Настройка SMS провайдера (для телефонной авторизации)

Настройте SMS провайдер через административную панель Bitrix:

**Настройки → Настройки продукта → SMS-провайдеры**

Выберите и настройте провайдер (SMSC.ru, SMS Aero, Twilio и др.).
Модуль автоматически использует настроенный в Bitrix SMS-провайдер для отправки кодов подтверждения.

## Первые шаги

### 1. Получение текущего пользователя

```php
use Beeralex\User\Contracts\UserRepositoryContract;

$userRepo = service(UserRepositoryContract::class);
$currentUser = $userRepo->getCurrentUser();

if ($currentUser->isAuthorized()) {
    echo "Привет, {$currentUser->getName()}!";
    echo "Email: {$currentUser->getEmail()}";
}
```

### 2. Авторизация по email

```php
use Beeralex\User\Auth\AuthService;
use Beeralex\User\Auth\AuthCredentialsDto;

$authService = service(AuthService::class);

$credentials = new AuthCredentialsDto(
    type: 'email',
    email: 'user@example.com',
    password: 'password123'
);

$result = $authService->login($credentials);

if ($result->isSuccess()) {
    $data = $result->getData();
    
    // Если JWT включен
    if (isset($data['accessToken'])) {
        echo "Access Token: {$data['accessToken']}";
        echo "Refresh Token: {$data['refreshToken']}";
    }
    
    echo "User ID: {$data['userId']}";
}
```

### 3. Регистрация нового пользователя

```php
$credentials = new AuthCredentialsDto(
    type: 'email',
    email: 'newuser@example.com',
    password: 'securePassword123',
    firstName: 'Иван',
    lastName: 'Петров'
);

$result = $authService->register($credentials);

if ($result->isSuccess()) {
    echo "Пользователь зарегистрирован!";
    // Автоматически авторизован
}
```

### 4. Работа с телефонами

```php
use Beeralex\User\Phone;

// Парсинг номера
$phone = Phone::fromString('+79991234567');

echo $phone->formatE164();         // +79991234567
echo $phone->formatInternational(); // +7 999 123-45-67
echo $phone->formatNational();      // (999) 123-45-67
echo $phone->getCountryCode();      // 7

// Валидация
if ($phone->isValid()) {
    echo "Номер валиден";
}
```

### 5. Обновление профиля

```php
use Beeralex\User\UserService;

$userService = service(UserService::class);
$currentUser = $userRepo->getCurrentUser();

$result = $userService->updateProfile($currentUser, [
    'NAME' => 'Новое имя',
    'LAST_NAME' => 'Новая фамилия',
    'PERSONAL_PHONE' => '+79991234567',
]);

if ($result->isSuccess()) {
    echo "Профиль обновлен";
}
```

### 6. Смена пароля

```php
$result = $userService->changePassword($currentUser, 'newPassword123');

if ($result->isSuccess()) {
    echo "Пароль изменен";
}
```

## Примеры интеграции

### Компонент авторизации

```php
<?php
use Beeralex\User\Auth\AuthService;
use Beeralex\User\Auth\AuthCredentialsDto;

class LoginComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        if ($this->request->isPost()) {
            $this->handleLogin();
        }
        
        $this->includeComponentTemplate();
    }
    
    protected function handleLogin(): void
    {
        $authService = service(AuthService::class);
        
        $credentials = new AuthCredentialsDto(
            type: 'email',
            email: $this->request->getPost('email'),
            password: $this->request->getPost('password')
        );
        
        $result = $authService->login($credentials);
        
        if ($result->isSuccess()) {
            LocalRedirect('/personal/');
        } else {
            $this->arResult['ERRORS'] = $result->getErrorMessages();
        }
    }
}
```

### REST API контроллер

```php
use Beeralex\Core\Http\Controllers\ApiController;
use Beeralex\User\Auth\AuthService;
use Beeralex\User\Auth\AuthCredentialsDto;

class AuthController extends ApiController
{
    public function loginAction(AuthCredentialsDto $credentials)
    {
        $authService = service(AuthService::class);
        $result = $authService->login($credentials, [
            'ip' => $_SERVER['REMOTE_ADDR'],
            'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
        ]);
        
        if ($result->isSuccess()) {
            $data = $result->getData();
            
            // Устанавливаем токены в httpOnly cookies
            $this->setCookie('access', $data['accessToken'], $data['accessTokenExpired']);
            $this->setCookie('refresh', $data['refreshToken'], $data['refreshTokenExpired']);
            
            // Удаляем токены из ответа (они уже в cookies)
            unset($data['accessToken'], $data['refreshToken'], $data['accessTokenExpired'], $data['refreshTokenExpired']);
            
            return $data;
        }
        
        return $result;
    }
    
    public function registerAction(AuthCredentialsDto $credentials)
    {
        $authService = service(AuthService::class);
        return $authService->register($credentials);
    }
    
    public function refreshAction()
    {
        // Refresh token извлекается из cookie
        $refreshToken = $_COOKIE['refresh'] ?? null;
        
        if (!$refreshToken) {
            http_response_code(401);
            return ['error' => 'No refresh token'];
        }
        
        $authService = service(AuthService::class);
        $result = $authService->refreshTokens($refreshToken);
        
        if ($result->isSuccess()) {
            $data = $result->getData();
            
            // Обновляем cookies
            $this->setCookie('access', $data['accessToken'], $data['accessTokenExpired']);
            $this->setCookie('refresh', $data['refreshToken'], $data['refreshTokenExpired']);
            
            return [];
        }
        
        return $result;
    }
    
    private function setCookie(
        string $name,
        string $value,
        int $expires,
        string $path = '/'
    ): void {
        global $APPLICATION;
        
        $APPLICATION->set_cookie(
            $name,
            $value,
            $expires,
            $path,
            false,    // domain
            true,     // httpOnly - защита от XSS
            true,     // secure - только HTTPS
            false,    // samesite
            true      // encrypt
        );
    }
}
```

### Middleware для проверки JWT

```php
use Beeralex\User\Auth\JwtTokenManager;

class JwtMiddleware
{
    public function handle()
    {
        $token = $this->getTokenFromCookie();
        
        if (!$token) {
            http_response_code(401);
            echo json_encode(['error' => 'Token required']);
            die();
        }
        
        $jwtManager = service(JwtTokenManager::class);
        $result = $jwtManager->validateAccessToken($token);
        
        if (!$result->isSuccess()) {
            http_response_code(401);
            echo json_encode(['error' => 'Invalid token']);
            die();
        }
        
        // Токен валиден, продолжаем
        $userId = $result->getData()['userId'];
    }
    
    protected function getTokenFromCookie(): ?string
    {
        // Сначала проверяем Authorization header (для мобильных приложений)
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }
        
        // Затем проверяем httpOnly cookie (для веб-приложений)
        return $_COOKIE['access'] ?? null;
    }
        }
        
        return null;
    }
}
```

## Типичные сценарии

### Авторизация через Telegram

```php
$credentials = new AuthCredentialsDto(
    type: 'telegram',
    socialData: [
        'id' => $telegramUserId,
        'first_name' => $firstName,
        'username' => $username,
        'auth_date' => $authDate,
        'hash' => $hash,
    ]
);

$result = $authService->login($credentials);
```

### Авторизация по телефону (двухэтапная)

```php
// Шаг 1: Отправка кода
$credentials = new AuthCredentialsDto(
    type: 'phone',
    phone: '+79991234567'
);

$result = $authManager->authenticate('phone', $credentials);
// Код отправлен на телефон

// Шаг 2: Проверка кода
$credentials = new AuthCredentialsDto(
    type: 'phone',
    phone: '+79991234567',
    codeVerify: '1234'
);

$result = $authService->login($credentials);
```

### Восстановление пароля

```php
use Beeralex\User\UserService;

$userService = service(UserService::class);

// Отправка письма с инструкцией
$result = $userService->restorePassword('user@example.com');

// Установка нового пароля по checkword
$result = $userService->changePasswordByCheckword(
    email: 'user@example.com',
    password: 'newPassword123',
    checkword: $checkwordFromEmail
);
```

## Структура модуля

```
lib/
├── User.php                    - Сущность пользователя
├── UserRepository.php          - Репозиторий
├── UserService.php             - Сервис
├── UserFactory.php             - Фабрика
├── UserBuilder.php             - Билдер для создания
├── Phone.php                   - Класс для телефонов
├── Auth/
│   ├── AuthManager.php         - Менеджер аутентификации
│   ├── AuthService.php         - Высокоуровневый сервис
│   ├── JwtTokenManager.php     - Управление JWT
│   ├── PhoneCodeService.php    - SMS коды
│   ├── Authenticators/         - Стратегии аутентификации
│   ├── Session/                - Управление сессиями
│   ├── Social/                 - Социальная авторизация
│   └── Validator/              - Валидаторы
└── Contracts/                  - Интерфейсы
```

## Следующие шаги

- [User, Repository, Service →](user-entity.md)
- [Система аутентификации →](authentication.md)
- [JWT токены →](jwt-tokens.md)
