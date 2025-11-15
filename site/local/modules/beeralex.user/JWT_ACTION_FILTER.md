# JWT Action Filter - Документация

## Описание

`JwtAuthFilter` - это Action Filter для REST API контроллеров Bitrix, который автоматически проверяет JWT токены перед выполнением действий контроллера.

## Преимущества ActionFilter подхода

✅ **Декларативная настройка** - указываете фильтр в `configureActions()`  
✅ **Гранулярный контроль** - разные действия контроллера могут требовать или не требовать JWT  
✅ **REST API Ready** - идеально подходит для REST API контроллеров  
✅ **Стандартизация** - следует архитектуре Bitrix Framework  
✅ **Обработка ошибок** - стандартные ошибки через Error objects  

## Установка

Action Filter уже включен в модуль `beeralex.user`. Просто используйте его в своих контроллерах.

## Использование

### Базовое использование в контроллере

```php
<?php
namespace Beeralex\User\Controller;

use Beeralex\User\Auth\ActionFilter\JwtAuthFilter;
use Bitrix\Main\Engine\Controller;

class MyApiController extends Controller
{
    public function configureActions(): array
    {
        return [
            'getData' => [
                'prefilters' => [
                    new JwtAuthFilter(),
                ],
            ],
        ];
    }

    /**
     * Этот метод требует валидный JWT токен
     */
    public function getDataAction(): array
    {
        global $USER;
        $userId = $USER->GetID(); // Пользователь уже авторизован фильтром
        
        return [
            'data' => 'some protected data',
            'userId' => $userId,
        ];
    }
}
```

### Опциональный токен

Если токен не обязателен, но если он есть - будет проверен:

```php
public function configureActions(): array
{
    return [
        'getPublicData' => [
            'prefilters' => [
                new JwtAuthFilter(['optional' => true]),
            ],
        ],
    ];
}

public function getPublicDataAction(): array
{
    global $USER;
    
    // Данные доступны всем, но если есть токен - покажем персональные
    if ($USER->IsAuthorized()) {
        return ['data' => 'personalized data for user ' . $USER->GetID()];
    }
    
    return ['data' => 'public data'];
}
```

### Комбинация с другими фильтрами

```php
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Engine\ActionFilter\Csrf;

public function configureActions(): array
{
    return [
        'createOrder' => [
            'prefilters' => [
                new JwtAuthFilter(),
                new HttpMethod([HttpMethod::METHOD_POST]),
                new Csrf(),
            ],
        ],
    ];
}
```

### Разные требования для разных действий

```php
public function configureActions(): array
{
    return [
        // Требует JWT
        'getProfile' => [
            'prefilters' => [new JwtAuthFilter()],
        ],
        
        // Не требует JWT
        'getPublicInfo' => [
            'prefilters' => [],
        ],
        
        // JWT опционален
        'getProducts' => [
            'prefilters' => [new JwtAuthFilter(['optional' => true])],
        ],
    ];
}
```

## Пример REST контроллера

```php
<?php
namespace Beeralex\User\Controller;

use Beeralex\User\Auth\ActionFilter\JwtAuthFilter;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter\HttpMethod;
use Bitrix\Main\Error;

class UserController extends Controller
{
    public function configureActions(): array
    {
        return [
            'profile' => [
                'prefilters' => [
                    new JwtAuthFilter(),
                    new HttpMethod([HttpMethod::METHOD_GET]),
                ],
            ],
            'updateProfile' => [
                'prefilters' => [
                    new JwtAuthFilter(),
                    new HttpMethod([HttpMethod::METHOD_POST]),
                ],
            ],
            'list' => [
                'prefilters' => [
                    new JwtAuthFilter(['optional' => true]),
                    new HttpMethod([HttpMethod::METHOD_GET]),
                ],
            ],
        ];
    }

    /**
     * GET /rest/beeralex.user.user/profile
     * Headers: Authorization: Bearer <token>
     */
    public function profileAction(): array
    {
        global $USER;
        $userId = $USER->GetID();
        $user = \CUser::GetByID($userId)->Fetch();

        return [
            'user' => [
                'id' => $user['ID'],
                'email' => $user['EMAIL'],
                'name' => $user['NAME'],
            ],
        ];
    }

    /**
     * POST /rest/beeralex.user.user/updateProfile
     * Headers: Authorization: Bearer <token>
     * Body: {"name": "John", "lastName": "Doe"}
     */
    public function updateProfileAction(string $name, string $lastName): array
    {
        global $USER;
        $userId = $USER->GetID();

        $user = new \CUser();
        $result = $user->Update($userId, [
            'NAME' => $name,
            'LAST_NAME' => $lastName,
        ]);

        if (!$result) {
            $this->addError(new Error($user->LAST_ERROR));
            return [];
        }

        return ['success' => true];
    }

    /**
     * GET /rest/beeralex.user.user/list
     * Headers: Authorization: Bearer <token> (опционально)
     */
    public function listAction(): array
    {
        global $USER;
        
        $filter = [];
        if ($USER->IsAuthorized()) {
            // Для авторизованных показываем больше данных
            $filter['ACTIVE'] = 'Y';
        }

        $users = \CUser::GetList(
            'id',
            'asc',
            $filter,
            ['FIELDS' => ['ID', 'LOGIN', 'NAME', 'LAST_NAME']]
        );

        $result = [];
        while ($user = $users->Fetch()) {
            $result[] = $user;
        }

        return ['users' => $result];
    }
}
```

## Регистрация контроллера

### 1. Создайте файл `ajax.php` в модуле

```php
// /local/modules/beeralex.user/lib/Controller/ajax.php
<?php
define('NOT_CHECK_PERMISSIONS', true);
require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

\Bitrix\Main\Loader::includeModule('beeralex.user');

// Роутинг контроллеров
$request = \Bitrix\Main\Context::getCurrent()->getRequest();
$controllerName = $request->get('controller') ?? 'auth';
$action = $request->get('action') ?? 'index';

// Маппинг контроллеров
$controllers = [
    'auth' => \Beeralex\User\Controller\AuthController::class,
    'user' => \Beeralex\User\Controller\UserController::class,
];

if (!isset($controllers[$controllerName])) {
    http_response_code(404);
    echo json_encode(['error' => 'Controller not found']);
    exit;
}

$controller = new $controllers[$controllerName]();
$response = $controller->run($action, $request->toArray());

header('Content-Type: application/json');
echo json_encode($response);
```

### 2. Или используйте встроенную маршрутизацию Bitrix

В `urlrewrite.php`:

```php
return [
    [
        'CONDITION' => '#^/rest/beeralex\\.user\\.([a-z]+)/([a-z]+)#',
        'RULE' => 'controller=$1&action=$2',
        'PATH' => '/local/modules/beeralex.user/lib/Controller/ajax.php',
    ],
];
```

## Клиентская часть (JavaScript)

```javascript
const API_BASE = '/rest/beeralex.user.auth';

class ApiClient {
  constructor() {
    this.token = localStorage.getItem('access_token');
  }

  setToken(token) {
    this.token = token;
    localStorage.setItem('access_token', token);
  }

  async request(endpoint, options = {}) {
    const headers = {
      'Content-Type': 'application/json',
      ...options.headers,
    };

    if (this.token) {
      headers['Authorization'] = `Bearer ${this.token}`;
    }

    const response = await fetch(`${API_BASE}${endpoint}`, {
      ...options,
      headers,
    });

    if (response.status === 401) {
      // Токен истёк, пытаемся обновить
      const refreshed = await this.refreshToken();
      if (refreshed) {
        // Повторяем запрос с новым токеном
        return this.request(endpoint, options);
      }
    }

    return response.json();
  }

  async login(email, password) {
    const data = await this.request('/login', {
      method: 'POST',
      body: JSON.stringify({
        type: 'email',
        email,
        password,
      }),
    });

    if (data.tokens) {
      this.setToken(data.tokens.access);
      localStorage.setItem('refresh_token', data.tokens.refresh);
    }

    return data;
  }

  async refreshToken() {
    const refreshToken = localStorage.getItem('refresh_token');
    if (!refreshToken) return false;

    try {
      const data = await this.request('/refresh', {
        method: 'POST',
        body: JSON.stringify({ refreshToken }),
      });

      if (data.tokens) {
        this.setToken(data.tokens.access);
        localStorage.setItem('refresh_token', data.tokens.refresh);
        return true;
      }
    } catch (e) {
      return false;
    }

    return false;
  }

  async getProfile() {
    return this.request('/profile');
  }

  async logout() {
    const data = await this.request('/logout', { method: 'POST' });
    localStorage.removeItem('access_token');
    localStorage.removeItem('refresh_token');
    this.token = null;
    return data;
  }
}

// Использование
const api = new ApiClient();

// Авторизация
await api.login('user@example.com', 'password123');

// Получение профиля
const profile = await api.getProfile();
console.log(profile.user);

// Выход
await api.logout();
```

## Коды ошибок

| Код | Описание |
|-----|----------|
| `invalid_token` | Невалидный токен |
| `token_expired` | Токен истёк |
| `token_missing` | Токен отсутствует |
| `jwt_disabled` | JWT авторизация отключена |

## Ответы API

### Успешный ответ

```json
{
  "status": "success",
  "data": {
    "user": {
      "id": 123,
      "email": "user@example.com"
    }
  }
}
```

### Ответ с ошибкой

```json
{
  "status": "error",
  "errors": [
    {
      "message": "Token has expired",
      "code": "token_expired"
    }
  ]
}
```

## Сравнение с Prefilter

| Аспект | ActionFilter | Prefilter (OnPageStart) |
|--------|--------------|------------------------|
| Применение | REST API контроллеры | Все запросы |
| Гранулярность | На уровне действий | На уровне путей |
| Конфигурация | В контроллере | В init.php |
| Ошибки | Через Error objects | HTTP 401 + JSON |
| Производительность | Только для API | Проверка на каждом запросе |

## Рекомендации

- Используйте **ActionFilter** для REST API контроллеров
- Используйте **Prefilter** (OnPageStart) для защиты обычных PHP endpoints
- Комбинируйте оба подхода для полной защиты приложения

## Дополнительно

См. также:
- [JWT_README.md](JWT_README.md) - полная документация JWT
- [JWT_PREFILTER_QUICKSTART.md](JWT_PREFILTER_QUICKSTART.md) - Prefilter подход
