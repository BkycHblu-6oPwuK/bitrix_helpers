# JWT токены

Документация по JwtTokenManager и работе с JWT токенами.

## Обзор

Модуль поддерживает JWT (JSON Web Tokens) аутентификацию с access/refresh токенами:
- **Access Token** — короткоживущий токен для доступа к API (1 час по умолчанию)
- **Refresh Token** — долгоживущий токен для обновления access токена (30 дней)
- Хранение сессий в БД с отслеживанием устройств
- Возможность отзыва токенов

## Настройка

Настройка JWT выполняется через административную панель:

**Настройки → Настройки модулей → Модуль пользователей (beeralex.user)**

Доступные параметры:
- ✅ **Включить JWT авторизацию**
- **Секретный ключ** - 256-битный ключ для подписи токенов
- **Алгоритм** - HS256 (рекомендуется), HS384, HS512
- **Время жизни access токена** - по умолчанию 1200 сек (20 минут)
- **Время жизни refresh токена** - по умолчанию 2592000 сек (30 дней)

⚠️ **Важно:** Используйте криптографически стойкий секретный ключ!

Генерация секретного ключа:
```bash
php -r "echo bin2hex(random_bytes(32));"
```

## JwtTokenManager

Центральный класс для работы с JWT токенами.

### Внедрение зависимостей

```php
use Beeralex\User\Auth\JwtTokenManager;

$jwtManager = service(JwtTokenManager::class);
```

### Проверка включения JWT

```php
if ($jwtManager->isEnabled()) {
    echo "JWT авторизация включена";
}
```

### Методы генерации

#### `generateAccessToken(int $userId, array $additionalClaims = []): Result`

Генерирует access токен.

```php
$result = $jwtManager->generateAccessToken(
    userId: 123,
    additionalClaims: [
        'auth_type' => 'email',
        'email' => 'user@example.com',
        'role' => 'admin',
    ]
);

if ($result->isSuccess()) {
    $data = $result->getData();
    
    $accessToken = $data['accessToken'];
    $expired = $data['accessTokenExpired']; // timestamp
    
    echo "Token: {$accessToken}";
    echo "Expires: " . date('Y-m-d H:i:s', $expired);
}
```

**Структура токена:**

```json
{
  "iss": "your-site.com",
  "iat": 1735000000,
  "exp": 1735003600,
  "sub": "123",
  "type": "access",
  "jti": "unique-token-id",
  "auth_type": "email",
  "email": "user@example.com"
}
```

#### `generateRefreshToken(int $userId): Result`

Генерирует refresh токен.

```php
$result = $jwtManager->generateRefreshToken(123);

if ($result->isSuccess()) {
    $data = $result->getData();
    
    $refreshToken = $data['refreshToken'];
    $expired = $data['refreshTokenExpired'];
}
```

#### `generateTokenPair(int $userId, array $additionalClaims = []): Result`

Генерирует пару токенов (access + refresh) и сохраняет сессию в БД.

```php
$result = $jwtManager->generateTokenPair(
    userId: 123,
    additionalClaims: [
        'auth_type' => 'email',
        'email' => 'user@example.com',
        'ip' => $_SERVER['REMOTE_ADDR'],
        'user_agent' => $_SERVER['HTTP_USER_AGENT'],
    ]
);

if ($result->isSuccess()) {
    $data = $result->getData();
    
    $accessToken = $data['accessToken'];
    $refreshToken = $data['refreshToken'];
    $accessExpired = $data['accessTokenExpired'];
    $refreshExpired = $data['refreshTokenExpired'];
}
```

### Методы валидации

#### `validateAccessToken(string $token): Result`

Проверяет access токен.

```php
$result = $jwtManager->validateAccessToken($bearerToken);

if ($result->isSuccess()) {
    $data = $result->getData();
    
    $userId = $data['userId'];
    $claims = $data['claims'];
    
    echo "User ID: {$userId}";
    echo "Auth Type: {$claims['auth_type']}";
} else {
    // Токен невалиден или истёк
    $errors = $result->getErrorMessages();
}
```

#### `validateRefreshToken(string $token): Result`

Проверяет refresh токен и сессию в БД.

```php
$result = $jwtManager->validateRefreshToken($refreshToken);

if ($result->isSuccess()) {
    $data = $result->getData();
    
    $userId = $data['userId'];
    $session = $data['session']; // Данные сессии из БД
} else {
    // Токен невалиден или отозван
}
```

### Обновление токенов

#### `refreshTokens(string $refreshToken): Result`

Обновляет пару токенов по refresh токену.

```php
$result = $jwtManager->refreshTokens($oldRefreshToken);

if ($result->isSuccess()) {
    $data = $result->getData();
    
    $newAccessToken = $data['accessToken'];
    $newRefreshToken = $data['refreshToken'];
    $accessExpired = $data['accessTokenExpired'];
    $refreshExpired = $data['refreshTokenExpired'];
    
    // Старый refresh токен автоматически отозван
    // Новые токены нужно сохранить на клиенте
}
```

**Процесс:**
1. Валидация старого refresh токена
2. Проверка сессии в БД
3. Генерация новой пары токенов
4. Отзыв старого refresh токена
5. Сохранение новой сессии

### Отзыв токенов

#### `revokeRefreshToken(string $token): void`

Отзывает refresh токен (удаляет сессию из БД).

```php
$jwtManager->revokeRefreshToken($refreshToken);
// Токен больше нельзя использовать
```

#### `revokeAllUserTokens(int $userId): void`

Отзывает все токены пользователя.

```php
$jwtManager->revokeAllUserTokens(123);
// Все сессии пользователя удалены
```

### Декодирование без валидации

#### `decodeToken(string $token): ?array`

Декодирует токен без проверки подписи (для отладки).

```php
$payload = $jwtManager->decodeToken($token);

print_r($payload);
// [
//   'sub' => '123',
//   'type' => 'access',
//   'auth_type' => 'email',
//   ...
// ]
```

## Сессии

### UserSessionRepository

Репозиторий для работы с сессиями пользователей.

```php
use Beeralex\User\Auth\Session\UserSessionRepository;

$sessionRepo = service(UserSessionRepository::class);
```

#### Создание сессии

```php
$sessionRepo->createSession(
    userId: 123,
    refreshToken: $refreshToken,
    userAgent: $_SERVER['HTTP_USER_AGENT'],
    ip: $_SERVER['REMOTE_ADDR']
);
```

#### Получение сессий пользователя

```php
$sessions = $sessionRepo->getUserSessions(123);

foreach ($sessions as $session) {
    echo "ID: {$session['ID']}\n";
    echo "IP: {$session['IP_ADDRESS']}\n";
    echo "User Agent: {$session['USER_AGENT']}\n";
    echo "Created: {$session['CREATED_AT']}\n";
    echo "Last Activity: {$session['LAST_ACTIVITY']}\n";
}
```

#### Удаление сессии

```php
// По токену
$sessionRepo->revokeSession($refreshToken);

// По ID
$sessionRepo->deleteSession($sessionId);
```

#### Удаление всех сессий пользователя

```php
$sessionRepo->revokeAllUserSessions(123);
```

#### Обновление активности

```php
$sessionRepo->updateLastActivity($refreshToken);
```

## Примеры использования

### REST API с JWT

#### Middleware для проверки токена

```php
class JwtMiddleware
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
            $this->sendError('Token required', 401);
            return null;
        }
        
        $result = $this->jwtManager->validateAccessToken($token);
        
        if (!$result->isSuccess()) {
            $this->sendError('Invalid or expired token', 401);
            return null;
        }
        
        return $result->getData()['userId'];
    }
    
    protected function extractToken(): ?string
    {
        // Сначала проверяем Authorization header (для мобильных приложений)
        $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        
        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }
        
        // Затем проверяем httpOnly cookie (для веб-приложений)
        return $_COOKIE['access'] ?? null;
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

#### Защищенный endpoint

```php
use Beeralex\Core\Http\Controllers\ApiController;

class ProtectedController extends ApiController
{
    public function indexAction()
    {
        $middleware = new JwtMiddleware();
        $userId = $middleware->handle();
        
        // Пользователь авторизован
        $userRepo = service(UserRepositoryContract::class);
        $user = $userRepo->getById($userId);
        
        return [
            'user' => [
                'id' => $user->getId(),
                'name' => $user->getFullName(),
                'email' => $user->getEmail(),
            ]
        ];
    }
}
```

### JavaScript клиент с автообновлением токенов

```javascript
class ApiClient {
    /**
     * Токены хранятся в httpOnly cookies и автоматически отправляются браузером
     */
    async request(url, options = {}) {
        // Включаем отправку cookies
        options.credentials = 'include';
        
        let response = await fetch(url, options);
        
        // Если 401 - пробуем обновить токен
        if (response.status === 401) {
            const refreshed = await this.refreshTokens();
            
            if (refreshed) {
                // Повторяем запрос (новые токены уже в cookies)
                response = await fetch(url, options);
            } else {
                // Не удалось обновить - редирект на логин
                window.location.href = '/login/';
                return;
            }
        }
        
        return response.json();
    }
    
    async refreshTokens() {
        try {
            const response = await fetch('/api/v1/auth/refresh/', {
                method: 'POST',
                credentials: 'include', // Отправляем refresh token из cookie
                headers: {'Content-Type': 'application/json'}
            });
            
            const data = await response.json();
            
            // Новые токены автоматически установлены в cookies сервером
            return data.status === 'success';
        } catch (error) {
            console.error('Token refresh failed:', error);
            return false;
        }
    }
    
    async login(email, password) {
        const response = await fetch('/api/v1/auth/login/', {
            method: 'POST',
            credentials: 'include',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                type: 'email',
                email,
                password
            })
        });
        
        const data = await response.json();
        
        // Токены установлены в httpOnly cookies автоматически
        return data.status === 'success';
    }
    
    async logout() {
        await fetch('/api/v1/auth/logout/', {
            method: 'POST',
            credentials: 'include',
            headers: {'Content-Type': 'application/json'}
        });
        
        // Cookies очищены на сервере
    }
}

// Использование
const api = new ApiClient();

// Логин
if (await api.login('user@example.com', 'password123')) {
    console.log('Успешный вход');
}

// Запросы к API (токены отправляются автоматически)
const userData = await api.request('/api/v1/user/profile/');
const orders = await api.request('/api/v1/orders/');
```

**Преимущества httpOnly cookies:**
- 🔒 Защита от XSS атак - JavaScript не может прочитать токен
- 🚀 Автоматическая отправка браузером
- 🛡️ Защита от CSRF с помощью SameSite
- 📦 Не нужно вручную управлять хранилищем

### Управление сессиями

#### Компонент списка сессий

```php
class UserSessionsComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        global $USER;
        $userId = $USER->GetID();
        
        if (!$userId) {
            LocalRedirect('/login/');
        }
        
        $sessionRepo = service(UserSessionRepository::class);
        $this->arResult['SESSIONS'] = $sessionRepo->getUserSessions($userId);
        
        // Обработка удаления сессии
        if ($this->request->isPost() && $sessionId = $this->request->getPost('delete_session')) {
            $sessionRepo->deleteSession((int)$sessionId);
            LocalRedirect($APPLICATION->GetCurPageParam());
        }
        
        $this->includeComponentTemplate();
    }
}
```

**Шаблон:**

```php
<h2>Активные сессии</h2>

<table class="table">
    <thead>
        <tr>
            <th>Устройство</th>
            <th>IP адрес</th>
            <th>Создана</th>
            <th>Последняя активность</th>
            <th></th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($arResult['SESSIONS'] as $session): ?>
        <tr>
            <td><?= htmlspecialchars($session['USER_AGENT']) ?></td>
            <td><?= htmlspecialchars($session['IP_ADDRESS']) ?></td>
            <td><?= $session['CREATED_AT'] ?></td>
            <td><?= $session['LAST_ACTIVITY'] ?></td>
            <td>
                <form method="post" style="display:inline">
                    <?= bitrix_sessid_post() ?>
                    <input type="hidden" name="delete_session" value="<?= $session['ID'] ?>">
                    <button type="submit" class="btn btn-sm btn-danger">
                        Завершить
                    </button>
                </form>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<form method="post">
    <?= bitrix_sessid_post() ?>
    <button type="submit" name="delete_all" class="btn btn-warning">
        Завершить все сессии кроме текущей
    </button>
</form>
```

### Отладка JWT токенов

```php
$jwtManager = service(JwtTokenManager::class);

// Декодировать без проверки
$payload = $jwtManager->decodeToken($token);

echo "Type: {$payload['type']}\n";
echo "User ID: {$payload['sub']}\n";
echo "Issued At: " . date('Y-m-d H:i:s', $payload['iat']) . "\n";
echo "Expires At: " . date('Y-m-d H:i:s', $payload['exp']) . "\n";
echo "Issuer: {$payload['iss']}\n";
echo "JTI: {$payload['jti']}\n";

// Проверить истечение
$now = time();
if ($payload['exp'] < $now) {
    echo "Token expired!";
}
```

## Безопасность

### Рекомендации

1. **Используйте HTTPS** — JWT токены должны передаваться только по HTTPS
2. **Сильный секретный ключ** — минимум 256 бит случайных данных
3. **Короткий TTL для access токена** — 15-60 минут
4. **Храните refresh токен безопасно** — HttpOnly cookie или secure storage
5. **Не храните чувствительные данные в токене** — токен можно декодировать без секретного ключа
6. **Реализуйте rate limiting** — защита от bruteforce при обновлении токенов
7. **Логируйте подозрительную активность** — множественные попытки обновления с одним токеном

### Обработка истечения токенов

```javascript
// Проверка истечения перед запросом
function isTokenExpired(token) {
    try {
        const payload = JSON.parse(atob(token.split('.')[1]));
        return payload.exp * 1000 < Date.now();
    } catch {
        return true;
    }
}

if (isTokenExpired(accessToken)) {
    await refreshTokens();
}
```

## Навигация

- [← Система аутентификации](authentication.md)
- [Работа с телефонами →](phone.md)
