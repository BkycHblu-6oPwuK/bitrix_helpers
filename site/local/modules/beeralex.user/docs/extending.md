# Расширение модуля

Руководство по расширению функциональности модуля пользователей.

## Обзор

Модуль построен на принципах SOLID и предоставляет множество точек расширения:
- **Кастомные аутентификаторы** — добавление новых методов авторизации
- **Валидаторы** — проверка данных при регистрации/входе
- **Переопределение через DI** — замена реализаций без изменения кода
- **События** — подписка на события модуля
- **Middleware** — обработка запросов

## Создание кастомного аутентификатора

### 1. Реализация интерфейса

Создайте класс, реализующий `AuthenticatorContract`:

```php
namespace Local\User\Auth;

use Beeralex\User\Auth\Contracts\AuthenticatorContract;
use Beeralex\User\Auth\AuthCredentialsDto;
use Bitrix\Main\Result;

class BiometricAuthenticator implements AuthenticatorContract
{
    public function getKey(): string
    {
        return 'biometric';
    }
    
    public function getTitle(): string
    {
        return 'Биометрия';
    }
    
    public function getDescription(): ?string
    {
        return 'Вход по отпечатку пальца или Face ID';
    }
    
    public function getLogoUrl(): ?string
    {
        return '/local/images/biometric-icon.svg';
    }
    
    public function isService(): bool
    {
        return false; // Не внешний сервис
    }
    
    public function authenticate(AuthCredentialsDto $credentials): Result
    {
        $result = new Result();
        
        // Получаем биометрические данные
        $biometricData = $credentials->additionalData['biometric_data'] ?? null;
        $deviceId = $credentials->additionalData['device_id'] ?? null;
        
        if (!$biometricData || !$deviceId) {
            $result->addError(new \Bitrix\Main\Error('Biometric data required'));
            return $result;
        }
        
        // Проверяем биометрию
        $userId = $this->verifyBiometric($biometricData, $deviceId);
        
        if (!$userId) {
            $result->addError(new \Bitrix\Main\Error('Biometric verification failed'));
            return $result;
        }
        
        // Авторизуем пользователя
        global $USER;
        $USER->Authorize($userId);
        
        $result->setData(['userId' => $userId]);
        
        return $result;
    }
    
    public function register(AuthCredentialsDto $credentials): Result
    {
        $result = new Result();
        
        // Биометрическая регистрация не поддерживается
        // Пользователь должен быть зарегистрирован другим способом
        $result->addError(new \Bitrix\Main\Error('Use another registration method'));
        
        return $result;
    }
    
    public function getAuthorizationUrlOrHtml(): ?array
    {
        // Не требуется для биометрии
        return null;
    }
    
    protected function verifyBiometric(string $data, string $deviceId): ?int
    {
        // Ваша логика проверки биометрии
        // Например, через внешний сервис или локальное хранилище
        
        // Поиск пользователя по device_id
        $user = \CUser::GetList(
            $by = 'ID',
            $order = 'ASC',
            ['UF_DEVICE_ID' => $deviceId]
        )->Fetch();
        
        if (!$user) {
            return null;
        }
        
        // Проверка биометрических данных
        $storedData = $user['UF_BIOMETRIC_DATA'];
        
        if ($this->compareBiometric($data, $storedData)) {
            return (int)$user['ID'];
        }
        
        return null;
    }
    
    protected function compareBiometric(string $data, string $stored): bool
    {
        // Сравнение биометрических данных
        // В реальности это сложный алгоритм
        return hash_equals($stored, hash('sha256', $data));
    }
}
```

### 2. Регистрация в DI

В `/local/.settings_extra.php`:

```php
use Local\User\Auth\BiometricAuthenticator;
use Beeralex\User\Auth\AuthManager;

return [
    'beeralex.user' => [
        'value' => [
            'container' => [
                BiometricAuthenticator::class => [
                    'className' => BiometricAuthenticator::class,
                ],
                
                // Переопределяем AuthManager, добавляя биометрию
                AuthManager::class => [
                    'className' => AuthManager::class,
                    'constructorParams' => static function() {
                        $authenticators = [
                            service(EmailAuthenticator::class),
                            service(PhoneAuthenticator::class),
                            service(BiometricAuthenticator::class), // Новый
                        ];
                        
                        // Добавляем социальные
                        $socialFactory = service(SocialAuthenticatorFactory::class);
                        $authenticators = array_merge(
                            $authenticators,
                            $socialFactory->makeAll()
                        );
                        
                        $validators = [
                            service(EmailValidator::class),
                            service(PhoneValidator::class),
                        ];
                        
                        return [$authenticators, $validators];
                    }
                ]
            ]
        ]
    ]
];
```

### 3. Использование

```php
use Beeralex\User\Auth\AuthService;
use Beeralex\User\Auth\AuthCredentialsDto;

$authService = service(AuthService::class);

$credentials = new AuthCredentialsDto(
    type: 'biometric',
    additionalData: [
        'biometric_data' => $_POST['biometric_data'],
        'device_id' => $_POST['device_id'],
    ]
);

$result = $authService->login($credentials);

if ($result->isSuccess()) {
    echo "Биометрическая авторизация успешна";
}
```

## Создание валидатора

### 1. Реализация интерфейса

```php
namespace Local\User\Auth;

use Beeralex\User\Auth\Contracts\ValidatorContract;
use Beeralex\User\Auth\AuthCredentialsDto;
use Bitrix\Main\Result;

class StrongPasswordValidator implements ValidatorContract
{
    protected int $minLength = 12;
    protected bool $requireUppercase = true;
    protected bool $requireLowercase = true;
    protected bool $requireDigits = true;
    protected bool $requireSpecialChars = true;
    
    public function getKey(): string
    {
        return 'strong_password';
    }
    
    public function validate(AuthCredentialsDto $credentials, string $action): Result
    {
        $result = new Result();
        
        // Проверяем только при регистрации и смене пароля
        if (!in_array($action, ['register', 'change_password'])) {
            return $result;
        }
        
        $password = $credentials->password;
        
        if (!$password) {
            return $result; // Нет пароля — не наша зона ответственности
        }
        
        // Минимальная длина
        if (strlen($password) < $this->minLength) {
            $result->addError(new \Bitrix\Main\Error(
                "Пароль должен содержать минимум {$this->minLength} символов"
            ));
        }
        
        // Заглавные буквы
        if ($this->requireUppercase && !preg_match('/[A-ZА-Я]/u', $password)) {
            $result->addError(new \Bitrix\Main\Error(
                'Пароль должен содержать заглавные буквы'
            ));
        }
        
        // Строчные буквы
        if ($this->requireLowercase && !preg_match('/[a-zа-я]/u', $password)) {
            $result->addError(new \Bitrix\Main\Error(
                'Пароль должен содержать строчные буквы'
            ));
        }
        
        // Цифры
        if ($this->requireDigits && !preg_match('/[0-9]/', $password)) {
            $result->addError(new \Bitrix\Main\Error(
                'Пароль должен содержать цифры'
            ));
        }
        
        // Специальные символы
        if ($this->requireSpecialChars && !preg_match('/[!@#$%^&*()_+\-=\[\]{};:\'",.<>\/?\\\\|`~]/', $password)) {
            $result->addError(new \Bitrix\Main\Error(
                'Пароль должен содержать специальные символы'
            ));
        }
        
        // Проверка на популярные пароли
        if ($this->isCommonPassword($password)) {
            $result->addError(new \Bitrix\Main\Error(
                'Этот пароль слишком распространён. Выберите другой'
            ));
        }
        
        return $result;
    }
    
    protected function isCommonPassword(string $password): bool
    {
        $commonPasswords = [
            'password123',
            'qwerty12345',
            '123456789',
            // ... загрузить из файла
        ];
        
        return in_array(strtolower($password), $commonPasswords);
    }
}
```

### 2. Регистрация валидатора

```php
return [
    'beeralex.user' => [
        'value' => [
            'container' => [
                AuthManager::class => [
                    'className' => AuthManager::class,
                    'constructorParams' => static function() {
                        // ...authenticators
                        
                        $validators = [
                            service(EmailValidator::class),
                            service(PhoneValidator::class),
                            service(StrongPasswordValidator::class), // Новый
                        ];
                        
                        return [$authenticators, $validators];
                    }
                ]
            ]
        ]
    ]
];
```

## Переопределение сервисов

### Кастомный UserRepository

```php
namespace Local\User;

use Beeralex\User\Repository\UserRepositoryContract;
use Beeralex\User\Entity\User;
use Beeralex\User\DTO\Phone;

class CustomUserRepository implements UserRepositoryContract
{
    protected UserRepositoryContract $baseRepository;
    
    public function __construct(UserRepositoryContract $baseRepository)
    {
        $this->baseRepository = $baseRepository;
    }
    
    public function getById(int $id): ?User
    {
        $user = $this->baseRepository->getById($id);
        
        // Дополнительная логика
        if ($user) {
            $this->logAccess($user);
        }
        
        return $user;
    }
    
    public function getByEmail(string $email): ?User
    {
        // Можем добавить кеширование
        $cacheKey = "user_email_{$email}";
        
        if ($cached = $this->getFromCache($cacheKey)) {
            return $cached;
        }
        
        $user = $this->baseRepository->getByEmail($email);
        
        if ($user) {
            $this->saveToCache($cacheKey, $user, 300);
        }
        
        return $user;
    }
    
    // Делегирование остальных методов
    public function getByPhone(Phone $phone): ?User
    {
        return $this->baseRepository->getByPhone($phone);
    }
    
    // ... другие методы
    
    protected function logAccess(User $user): void
    {
        // Логирование доступа к пользователю
        \CEventLog::Add([
            'SEVERITY' => 'INFO',
            'AUDIT_TYPE_ID' => 'USER_ACCESS',
            'MODULE_ID' => 'local.user',
            'DESCRIPTION' => "User {$user->getId()} accessed"
        ]);
    }
}
```

### Регистрация в DI

```php
// /local/.settings_extra.php
use Beeralex\User\Repository\UserRepositoryContract;
use Beeralex\User\Repository\UserRepository;
use Local\User\CustomUserRepository;

return [
    'beeralex.user' => [
        'value' => [
            'container' => [
                UserRepositoryContract::class => [
                    'className' => CustomUserRepository::class,
                    'constructorParams' => static function() {
                        // Создаём базовый репозиторий
                        $baseRepo = new UserRepository(
                            service(UserFactoryContract::class),
                            service(FileService::class)
                        );
                        
                        return [$baseRepo];
                    }
                ]
            ]
        ]
    ]
];
```

## Middleware для API

### JWT Middleware с логированием

```php
namespace Local\User\Middleware;

use Beeralex\User\Auth\JwtTokenManager;

class LoggingJwtMiddleware
{
    protected JwtTokenManager $jwtManager;
    
    public function __construct()
    {
        $this->jwtManager = service(JwtTokenManager::class);
    }
    
    public function handle(): ?int
    {
        $token = $this->extractToken();
        
        if (!$token) {
            $this->log('No token provided', $_SERVER['REQUEST_URI']);
            $this->sendError('Token required', 401);
            return null;
        }
        
        $result = $this->jwtManager->validateAccessToken($token);
        
        if (!$result->isSuccess()) {
            $errors = $result->getErrorMessages();
            $this->log('Invalid token: ' . implode(', ', $errors), $_SERVER['REQUEST_URI']);
            $this->sendError('Invalid or expired token', 401);
            return null;
        }
        
        $userId = $result->getData()['userId'];
        
        $this->log("User {$userId} authenticated", $_SERVER['REQUEST_URI']);
        
        return $userId;
    }
    
    protected function extractToken(): ?string
    {
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }
        
        return null;
    }
    
    protected function log(string $message, string $uri): void
    {
        \CEventLog::Add([
            'SEVERITY' => 'INFO',
            'AUDIT_TYPE_ID' => 'API_ACCESS',
            'MODULE_ID' => 'local.api',
            'DESCRIPTION' => "{$message} | URI: {$uri}"
        ]);
    }
    
    protected function sendError(string $message, int $code): void
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode(['error' => $message]);
        die();
    }
}
```

### Rate Limiting Middleware

```php
namespace Local\User\Middleware;

class RateLimitMiddleware
{
    protected int $maxRequests = 60;
    protected int $perSeconds = 60;
    
    public function handle(?int $userId = null): bool
    {
        $key = $userId ? "user_{$userId}" : $_SERVER['REMOTE_ADDR'];
        
        $cacheKey = "rate_limit_{$key}";
        $requests = (int)(\Bitrix\Main\Data\Cache::createInstance()->get($cacheKey) ?? 0);
        
        if ($requests >= $this->maxRequests) {
            http_response_code(429);
            header('Content-Type: application/json');
            header('Retry-After: ' . $this->perSeconds);
            echo json_encode(['error' => 'Too many requests']);
            die();
        }
        
        // Увеличиваем счётчик
        \Bitrix\Main\Data\Cache::createInstance()->set(
            $cacheKey,
            $requests + 1,
            $this->perSeconds
        );
        
        return true;
    }
}
```

### Использование middleware

```php
use Beeralex\Core\Http\Controllers\ApiController;
use Local\User\Middleware\LoggingJwtMiddleware;
use Local\User\Middleware\RateLimitMiddleware;

class ProtectedController extends ApiController
{
    public function indexAction()
    {
        // Rate limiting
        $rateLimiter = new RateLimitMiddleware();
        $rateLimiter->handle();
        
        // JWT авторизация с логированием
        $jwtMiddleware = new LoggingJwtMiddleware();
        $userId = $jwtMiddleware->handle();
        
        // Бизнес-логика
        $userRepo = service(UserRepositoryContract::class);
        $user = $userRepo->getById($userId);
        
        return [
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getFullName(),
            ]
        ];
    }
}
```

## События модуля

### Подписка на события

```php
// /local/php_interface/init.php

use Bitrix\Main\EventManager;

$eventManager = EventManager::getInstance();

// После успешной регистрации
$eventManager->addEventHandler(
    'beeralex.user',
    'OnAfterUserRegister',
    function($event) {
        $user = $event->getParameter('user');
        $credentials = $event->getParameter('credentials');
        
        // Отправка welcome email
        \CEvent::Send('USER_REGISTERED', SITE_ID, [
            'USER_ID' => $user->getId(),
            'EMAIL' => $user->getEmail(),
            'NAME' => $user->getName(),
        ]);
        
        // Создание начального профиля
        // ...
    }
);

// После успешного входа
$eventManager->addEventHandler(
    'beeralex.user',
    'OnAfterUserLogin',
    function($event) {
        $userId = $event->getParameter('userId');
        $authenticator = $event->getParameter('authenticator');
        
        // Логирование входа
        \CEventLog::Add([
            'SEVERITY' => 'INFO',
            'AUDIT_TYPE_ID' => 'USER_LOGIN',
            'MODULE_ID' => 'beeralex.user',
            'USER_ID' => $userId,
            'DESCRIPTION' => "User logged in via {$authenticator}"
        ]);
    }
);

// Перед сменой пароля
$eventManager->addEventHandler(
    'beeralex.user',
    'OnBeforePasswordChange',
    function($event) {
        $userId = $event->getParameter('userId');
        $newPassword = $event->getParameter('newPassword');
        
        // Проверка на старые пароли
        if ($this->isPasswordUsedBefore($userId, $newPassword)) {
            $event->addError('Вы уже использовали этот пароль ранее');
            return false;
        }
    }
);
```

## Расширение DTO

### Кастомный AuthCredentialsDto

```php
namespace Local\User\DTO;

use Beeralex\User\Auth\AuthCredentialsDto as BaseDto;

class ExtendedAuthCredentialsDto extends BaseDto
{
    public function __construct(
        string $type,
        ?string $email = null,
        ?string $password = null,
        ?string $phone = null,
        ?string $codeVerify = null,
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $group = null,
        ?array $socialData = null,
        
        // Дополнительные поля
        public readonly ?string $patronymic = null,
        public readonly ?string $birthday = null,
        public readonly ?string $companyName = null,
        public readonly ?string $inn = null,
        public readonly ?array $customFields = null,
    ) {
        parent::__construct(
            $type,
            $email,
            $password,
            $phone,
            $codeVerify,
            $firstName,
            $lastName,
            $group,
            $socialData
        );
    }
}
```

### Использование

```php
use Local\User\DTO\ExtendedAuthCredentialsDto;

$credentials = new ExtendedAuthCredentialsDto(
    type: 'email',
    email: 'user@example.com',
    password: 'password123',
    firstName: 'Иван',
    lastName: 'Иванов',
    patronymic: 'Петрович',
    birthday: '1990-01-01',
    companyName: 'ООО "Рога и Копыта"',
    inn: '1234567890',
    customFields: [
        'UF_DEPARTMENT' => 'Отдел продаж',
        'UF_POSITION' => 'Менеджер',
    ]
);
```

## Тестирование

### PHPUnit тесты

```php
use PHPUnit\Framework\TestCase;
use Local\User\Auth\BiometricAuthenticator;
use Beeralex\User\Auth\AuthCredentialsDto;

class BiometricAuthenticatorTest extends TestCase
{
    protected BiometricAuthenticator $authenticator;
    
    protected function setUp(): void
    {
        $this->authenticator = new BiometricAuthenticator();
    }
    
    public function testGetKey()
    {
        $this->assertEquals('biometric', $this->authenticator->getKey());
    }
    
    public function testAuthenticateWithoutData()
    {
        $credentials = new AuthCredentialsDto(type: 'biometric');
        
        $result = $this->authenticator->authenticate($credentials);
        
        $this->assertFalse($result->isSuccess());
        $this->assertStringContainsString(
            'Biometric data required',
            $result->getErrorMessages()[0]
        );
    }
    
    public function testAuthenticateSuccess()
    {
        // Подготовка тестового пользователя
        $userId = $this->createTestUser();
        
        $credentials = new AuthCredentialsDto(
            type: 'biometric',
            additionalData: [
                'biometric_data' => 'test_data',
                'device_id' => 'test_device'
            ]
        );
        
        $result = $this->authenticator->authenticate($credentials);
        
        $this->assertTrue($result->isSuccess());
        $this->assertEquals($userId, $result->getData()['userId']);
    }
}
```

## Примеры готовых расширений

### Email подтверждение при регистрации

```php
namespace Local\User\Service;

use Beeralex\User\Service\UserService;
use Beeralex\User\Entity\User;

class EmailConfirmationService
{
    protected UserService $userService;
    
    public function __construct()
    {
        $this->userService = service(UserService::class);
    }
    
    public function sendConfirmationEmail(User $user): bool
    {
        $token = $this->generateToken($user);
        
        // Сохраняем токен
        $this->saveToken($user->getId(), $token);
        
        // Отправляем email
        return \CEvent::Send('USER_CONFIRM_EMAIL', SITE_ID, [
            'USER_ID' => $user->getId(),
            'EMAIL' => $user->getEmail(),
            'NAME' => $user->getName(),
            'CONFIRM_URL' => "https://site.com/confirm/?token={$token}"
        ]);
    }
    
    public function confirmEmail(string $token): bool
    {
        $userId = $this->getUserIdByToken($token);
        
        if (!$userId) {
            return false;
        }
        
        // Подтверждаем email
        $user = new \CUser;
        $user->Update($userId, [
            'UF_EMAIL_CONFIRMED' => true
        ]);
        
        // Удаляем токен
        $this->deleteToken($token);
        
        return true;
    }
    
    protected function generateToken(User $user): string
    {
        return hash('sha256', $user->getId() . time() . random_bytes(32));
    }
}
```

## Навигация

- [← Социальная аутентификация](social-auth.md)
- [Главная →](README.md)
