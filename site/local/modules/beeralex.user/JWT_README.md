# JWT Authentication для модуля beeralex.user

## Описание

Модуль поддерживает генерацию и валидацию JWT токенов с использованием библиотеки Firebase JWT.
JWT авторизация реализована через **Action Filter** для REST API контроллеров Bitrix.

## Установка

1. Установите зависимости:
```bash
composer require firebase/php-jwt:^6.0
```

2. Настройте модуль в админке Bitrix:
   - Перейдите в настройки модуля `beeralex.user`
   - Включите "Авторизацию по JWT токенам"
   - Укажите секретный ключ (рекомендуется использовать случайную строку длиной 32+ символов)

## Настройки модуля

- **BEERALEX_USER_ENABLE_JWT_AUTH** - включить/выключить JWT авторизацию (Y/N)
- **BEERALEX_USER_JWT_SECRET_KEY** - секретный ключ для подписи токенов
- **BEERALEX_USER_JWT_TTL** - время жизни access токена в секундах (по умолчанию 3600 = 1 час)
- **BEERALEX_USER_JWT_REFRESH_TTL** - время жизни refresh токена в секундах (по умолчанию 2592000 = 30 дней)
- **BEERALEX_USER_JWT_ALGORITHM** - алгоритм шифрования (по умолчанию HS256)
- **BEERALEX_USER_JWT_ISSUER** - издатель токенов (по умолчанию beeralex.user)

## Основные компоненты

### 1. JwtTokenManager - генерация и валидация токенов

```php
use Beeralex\User\Auth\JwtTokenManager;

$jwtManager = service(JwtTokenManager::class);

// Генерация пары токенов
$tokens = $jwtManager->generateTokenPair(123, ['email' => 'user@example.com']);

// Валидация токена
$decoded = $jwtManager->verifyToken($token);
$userId = (int)$decoded->sub;

// Обновление токенов
$newTokens = $jwtManager->refreshTokens($refreshToken);
```

### 2. JwtAuthFilter - Action Filter для контроллеров

```php
use Beeralex\User\Auth\ActionFilter\JwtAuthFilter;
use Bitrix\Main\Engine\Controller;

class MyApiController extends Controller
{
    public function configureActions(): array
    {
        return [
            'getData' => [
                'prefilters' => [new JwtAuthFilter()],
            ],
        ];
    }

    public function getDataAction(): array
    {
        global $USER;
        return ['userId' => $USER->GetID()];
    }
}
```

## Примеры использования

### REST API контроллер

```php
use Beeralex\User\Auth\ActionFilter\JwtAuthFilter;
use Bitrix\Main\Engine\Controller;

class UserController extends Controller
{
    public function configureActions(): array
    {
        return [
            'profile' => ['prefilters' => [new JwtAuthFilter()]],
            'publicInfo' => ['prefilters' => [new JwtAuthFilter(['optional' => true])]],
        ];
    }

    public function profileAction(): array
    {
        global $USER;
        $user = \CUser::GetByID($USER->GetID())->Fetch();
        return ['user' => $user];
    }
}
```

**Запрос:**
```bash
GET /rest/beeralex.user.user/profile
Headers: Authorization: Bearer eyJ0eXAiOiJKV1Qi...
```

### JavaScript клиент

```javascript
class ApiClient {
  async login(email, password) {
    const response = await fetch('/rest/beeralex.user.auth/login', {
      method: 'POST',
      headers: {'Content-Type': 'application/json'},
      body: JSON.stringify({email, password, type: 'email'}),
    });
    
    const data = await response.json();
    localStorage.setItem('access_token', data.tokens.access);
    localStorage.setItem('refresh_token', data.tokens.refresh);
    return data;
  }

  async request(url) {
    const token = localStorage.getItem('access_token');
    const response = await fetch(url, {
      headers: {'Authorization': `Bearer ${token}`},
    });
    return response.json();
  }
}

const api = new ApiClient();
await api.login('user@example.com', 'password');
const profile = await api.request('/rest/beeralex.user.user/profile');
```

## Безопасность

1. **Используйте HTTPS** - всегда передавайте токены по HTTPS
2. **Секретный ключ** - минимум 32 символа, случайный
3. **Время жизни токенов** - access: 1 час, refresh: 30 дней
4. **Хранение** - httpOnly cookies или localStorage с осторожностью

## Troubleshooting

- **"JWT secret key is not configured"** - укажите секретный ключ в настройках
- **"Token has expired"** - используйте refresh токен
- **"Invalid token signature"** - проверьте секретный ключ
- **Токен не извлекается** - добавьте в `.htaccess`:
```apache
RewriteCond %{HTTP:Authorization} ^(.*)
RewriteRule .* - [e=HTTP_AUTHORIZATION:%1]
```

## Дополнительно

См. [JWT_ACTION_FILTER.md](JWT_ACTION_FILTER.md) для подробной документации.
