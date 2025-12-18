# Система аутентификации

Документация по AuthManager, AuthService и аутентификаторам.

## Обзор

Модуль использует паттерн Strategy для различных методов аутентификации:
- Email + пароль
- Телефон + SMS код
- Социальные сети (Telegram, OAuth)
- Empty (авторизация по userId без проверки)

## AuthManager

Центральный менеджер аутентификации, управляющий различными стратегиями.

### Архитектура

```php
class AuthManager
{
    public function __construct(
        public readonly array $authenticators,  // Стратегии
        public readonly array $validators = []  // Валидаторы
    ) {}
}
```

### Методы

#### `authenticate(string $type, AuthCredentialsDto $userDto): Result`

Аутентифицирует пользователя через выбранный метод.

```php
use Beeralex\User\Auth\AuthManager;
use Beeralex\User\Auth\AuthCredentialsDto;

$authManager = service(AuthManager::class);

$credentials = new AuthCredentialsDto(
    type: 'email',
    email: 'user@example.com',
    password: 'password123'
);

$result = $authManager->authenticate('email', $credentials);

if ($result->isSuccess()) {
    $data = $result->getData();
    echo "User ID: {$data['userId']}";
    echo "Auth Type: {$data['authType']}";
}
```

#### `register(string $type, AuthCredentialsDto $userDto): Result`

Регистрирует нового пользователя.

```php
$credentials = new AuthCredentialsDto(
    type: 'email',
    email: 'newuser@example.com',
    password: 'securePassword123',
    firstName: 'Иван',
    lastName: 'Петров'
);

$result = $authManager->register('email', $credentials);

if ($result->isSuccess()) {
    $data = $result->getData();
    echo "Создан пользователь ID: {$data['userId']}";
    // Автоматически авторизован
}
```

#### `getAuthorizationUrlOrHtml(string $type): Result`

Получает URL или HTML для авторизации через внешний сервис.

```php
// Для Telegram
$result = $authManager->getAuthorizationUrlOrHtml('telegram');

if ($result->isSuccess()) {
    $data = $result->getData();
    echo $data['value']; // HTML кнопка Telegram
}
```

#### `getAvailable(): array`

Возвращает список доступных методов аутентификации.

```php
$methods = $authManager->getAvailable();
// ['email', 'phone', 'telegram', 'empty']
```

## AuthService

Высокоуровневый сервис для работы с аутентификацией и JWT токенами.

### Конструктор

```php
class AuthService
{
    public function __construct(
        protected readonly AuthManager $authManager,
        protected readonly JwtTokenManager $jwtManager
    ) {}
}
```

### Методы

#### `login(AuthCredentialsDto $credentials, array $metadata = []): Result`

Логин с выдачей JWT токенов (если включены).

```php
use Beeralex\User\Auth\AuthService;
use Beeralex\User\Auth\AuthCredentialsDto;

$authService = service(AuthService::class);

$credentials = new AuthCredentialsDto(
    type: 'email',
    email: 'user@example.com',
    password: 'password123'
);

$result = $authService->login($credentials, [
    'ip' => $_SERVER['REMOTE_ADDR'],
    'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? '',
]);

if ($result->isSuccess()) {
    $data = $result->getData();
    
    echo "User ID: {$data['userId']}\n";
    echo "Auth Type: {$data['authType']}\n";
    
    // Если JWT включен
    if (isset($data['accessToken'])) {
        echo "Access Token: {$data['accessToken']}\n";
        echo "Refresh Token: {$data['refreshToken']}\n";
        echo "Access Expires: {$data['accessTokenExpired']}\n";
        echo "Refresh Expires: {$data['refreshTokenExpired']}\n";
    }
}
```

**Возвращаемые данные:**

```php
[
    'userId' => 123,
    'authType' => 'email',
    'accessToken' => 'eyJ0eXAiOiJKV1QiLCJhbGc...',
    'refreshToken' => 'eyJ0eXAiOiJKV1QiLCJhbGc...',
    'accessTokenExpired' => 1735123456,
    'refreshTokenExpired' => 1737715456,
]
```

#### `register(AuthCredentialsDto $credentials): Result`

Регистрация с автоматическим логином.

```php
$credentials = new AuthCredentialsDto(
    type: 'email',
    email: 'newuser@example.com',
    password: 'password123',
    firstName: 'Петр',
    lastName: 'Сидоров'
);

$result = $authService->register($credentials);

if ($result->isSuccess()) {
    $data = $result->getData();
    // Пользователь создан и авторизован
    // Токены выданы (если JWT включен)
}
```

#### `refreshTokens(string $refreshToken): Result`

Обновление пары токенов.

```php
$result = $authService->refreshTokens($oldRefreshToken);

if ($result->isSuccess()) {
    $data = $result->getData();
    
    $newAccessToken = $data['accessToken'];
    $newRefreshToken = $data['refreshToken'];
    
    // Сохранить новые токены на клиенте
}
```

#### `logout(?string $refreshToken = null, bool $logoutFromBitrix = false): void`

Выход из системы с отзывом токена.

```php
// Выход только из JWT сессии
$authService->logout($refreshToken);

// Выход из JWT + Bitrix сессии
$authService->logout($refreshToken, logoutFromBitrix: true);
```

## AuthCredentialsDto

DTO для передачи данных аутентификации.

### Конструктор

```php
class AuthCredentialsDto
{
    public function __construct(
        public readonly string $type,           // Тип: email, phone, telegram
        public readonly ?string $email = null,
        public readonly ?string $password = null,
        public readonly ?string $phone = null,
        public readonly ?string $codeVerify = null,
        public readonly ?string $firstName = null,
        public readonly ?string $lastName = null,
        public readonly ?array $group = null,
        public readonly ?array $socialData = null,
    ) {}
}
```

### Примеры создания

**Email авторизация:**

```php
$credentials = new AuthCredentialsDto(
    type: 'email',
    email: 'user@example.com',
    password: 'password123'
);
```

**Телефон авторизация:**

```php
// Шаг 1: запрос кода
$credentials = new AuthCredentialsDto(
    type: 'phone',
    phone: '+79991234567'
);

// Шаг 2: проверка кода
$credentials = new AuthCredentialsDto(
    type: 'phone',
    phone: '+79991234567',
    codeVerify: '1234'
);
```

**Регистрация:**

```php
$credentials = new AuthCredentialsDto(
    type: 'email',
    email: 'newuser@example.com',
    password: 'securePass123',
    firstName: 'Иван',
    lastName: 'Петров',
    group: [2, 5] // Группы пользователя
);
```

**Telegram:**

```php
$credentials = new AuthCredentialsDto(
    type: 'telegram',
    socialData: [
        'id' => $telegramId,
        'first_name' => $firstName,
        'username' => $username,
        'auth_date' => $authDate,
        'hash' => $hash,
    ]
);
```

## Аутентификаторы

### EmailAuthenticator

Аутентификация по email и паролю.

```php
use Beeralex\User\Auth\Contracts\EmailAuthenticatorContract;

$emailAuth = service(EmailAuthenticatorContract::class);

$credentials = new AuthCredentialsDto(
    type: 'email',
    email: 'user@example.com',
    password: 'password123'
);

$result = $emailAuth->authenticate($credentials);
```

**Прямой вызов:**

```php
$result = $emailAuth->authenticateByEmail(
    email: 'user@example.com',
    password: 'password123'
);
```

### PhoneAuthenticator

Двухэтапная аутентификация по телефону с SMS кодом.

```php
use Beeralex\User\Auth\Contracts\PhoneAuthentificatorContract;
use Beeralex\User\Phone;

$phoneAuth = service(PhoneAuthentificatorContract::class);

// Шаг 1: Отправка кода
$phone = Phone::fromString('+79991234567');
$result = $phoneAuth->authenticateByPhone($phone);

if (!$result->isSuccess()) {
    // Код отправлен (ошибка означает "требуется код")
    echo "SMS код отправлен на {$phone->formatInternational()}";
}

// Шаг 2: Проверка кода
$result = $phoneAuth->authenticateByPhone($phone, code: '1234');

if ($result->isSuccess()) {
    echo "Авторизация успешна";
}
```

### EmptyAuthenticator

Авторизация по userId без проверки (для внутреннего использования).

```php
use Beeralex\User\Auth\Authenticators\EmptyAuthentificator;

$emptyAuth = service(EmptyAuthentificator::class);

// Авторизовать пользователя по ID
$emptyAuth->authorizeByUserId(123);

// Проверка авторизации Bitrix
if ($emptyAuth->bitrixIsAuthorized()) {
    echo "Пользователь авторизован в Bitrix";
}
```

### SocialAuthenticator

Авторизация через социальные сети (Telegram, OAuth).

См. [Социальная авторизация](social-auth.md)

## Валидаторы

### AuthEmailValidator

Валидирует email и пароль.

```php
use Beeralex\User\Auth\Validator\AuthEmailValidator;

$validator = service(AuthEmailValidator::class);

// Валидация для логина
$result = $validator->validateForLogin($credentials);

// Валидация для регистрации
$result = $validator->validateForRegistration($credentials);

if (!$result->isSuccess()) {
    foreach ($result->getErrors() as $error) {
        echo $error->getMessage();
    }
}
```

**Проверяет:**
- Email не пустой и валиден
- Пароль не пустой
- При регистрации: email уникален

### AuthPhoneValidator

Валидирует телефон и код.

```php
use Beeralex\User\Auth\Validator\AuthPhoneValidator;

$validator = service(AuthPhoneValidator::class);

$result = $validator->validateForLogin($credentials);
```

**Проверяет:**
- Телефон не пустой
- Телефон валиден (формат E164)
- При регистрации: телефон уникален

## Примеры использования

### REST API авторизация

```php
// api/v1/auth/login
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
        
        return $result;
    }
    
    public function registerAction(AuthCredentialsDto $credentials)
    {
        $authService = service(AuthService::class);
        return $authService->register($credentials);
    }
    
    public function refreshAction()
    {
        $refreshToken = $this->request->getPost('refreshToken');
        
        $authService = service(AuthService::class);
        return $authService->refreshTokens($refreshToken);
    }
    
    public function logoutAction()
    {
        $refreshToken = $this->request->getPost('refreshToken');
        
        $authService = service(AuthService::class);
        $authService->logout($refreshToken);
        
        return new Result();
    }
}
```

**JavaScript клиент:**

```javascript
// Логин
const response = await fetch('/api/v1/auth/login/', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        type: 'email',
        email: 'user@example.com',
        password: 'password123'
    })
});

const data = await response.json();
if (data.status === 'success') {
    localStorage.setItem('accessToken', data.data.accessToken);
    localStorage.setItem('refreshToken', data.data.refreshToken);
}

// Обновление токена
const refreshResponse = await fetch('/api/v1/auth/refresh/', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        refreshToken: localStorage.getItem('refreshToken')
    })
});
```

### Форма авторизации

```php
<?php
use Beeralex\User\Auth\AuthService;
use Beeralex\User\Auth\AuthCredentialsDto;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && check_bitrix_sessid()) {
    $authService = service(AuthService::class);
    
    $credentials = new AuthCredentialsDto(
        type: 'email',
        email: $_POST['email'],
        password: $_POST['password']
    );
    
    $result = $authService->login($credentials);
    
    if ($result->isSuccess()) {
        LocalRedirect('/personal/');
    } else {
        $errors = $result->getErrorMessages();
    }
}
?>

<form method="post">
    <?= bitrix_sessid_post() ?>
    
    <?php if (!empty($errors)): ?>
        <div class="alert alert-danger">
            <?php foreach ($errors as $error): ?>
                <p><?= htmlspecialchars($error) ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <div class="form-group">
        <label>Email</label>
        <input type="email" name="email" class="form-control" required>
    </div>
    
    <div class="form-group">
        <label>Пароль</label>
        <input type="password" name="password" class="form-control" required>
    </div>
    
    <button type="submit" class="btn btn-primary">Войти</button>
    <a href="/forgot-password/">Забыли пароль?</a>
</form>
```

### Телефонная авторизация (двухэтапная)

```php
<?php
use Beeralex\User\Auth\AuthManager;
use Beeralex\User\Auth\AuthCredentialsDto;

$authManager = service(AuthManager::class);

// Шаг 1: Запрос кода
if ($_POST['phone'] && !$_POST['code']) {
    $credentials = new AuthCredentialsDto(
        type: 'phone',
        phone: $_POST['phone']
    );
    
    $result = $authManager->authenticate('phone', $credentials);
    
    if (!$result->isSuccess()) {
        // Это нормально - код отправлен
        $codeSent = true;
        $phoneNumber = $_POST['phone'];
    }
}

// Шаг 2: Проверка кода
if ($_POST['phone'] && $_POST['code']) {
    $credentials = new AuthCredentialsDto(
        type: 'phone',
        phone: $_POST['phone'],
        codeVerify: $_POST['code']
    );
    
    $authService = service(AuthService::class);
    $result = $authService->login($credentials);
    
    if ($result->isSuccess()) {
        LocalRedirect('/personal/');
    } else {
        $errors = ['Неверный код'];
    }
}
?>

<form method="post">
    <?= bitrix_sessid_post() ?>
    
    <?php if (empty($codeSent)): ?>
        <div class="form-group">
            <label>Номер телефона</label>
            <input type="tel" name="phone" class="form-control" 
                   placeholder="+79991234567" required>
        </div>
        <button type="submit" class="btn-primary">Получить код</button>
    <?php else: ?>
        <input type="hidden" name="phone" value="<?= htmlspecialchars($phoneNumber) ?>">
        <p>Код отправлен на номер <?= htmlspecialchars($phoneNumber) ?></p>
        <div class="form-group">
            <label>Введите код из SMS</label>
            <input type="text" name="code" class="form-control" 
                   maxlength="4" required autofocus>
        </div>
        <button type="submit" class="btn btn-primary">Подтвердить</button>
    <?php endif; ?>
</form>
```

## Навигация

- [← User, Repository, Service](user-entity.md)
- [JWT токены →](jwt-tokens.md)
- [Социальная авторизация →](social-auth.md)
