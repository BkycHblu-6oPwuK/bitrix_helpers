# Социальная аутентификация

Документация по интеграции с социальными сетями и OAuth провайдерами.

## Обзор

Модуль поддерживает аутентификацию через социальные сети на базе Bitrix Social Services:
- **Встроенная интеграция** с Bitrix OAuth (Google, Yandex, VK, и др.)
- **Адаптер** для унифицированной работы с разными провайдерами
- **Фабрика аутентификаторов** для автоматического подключения активных сервисов
- **Расширяемая архитектура** для добавления custom провайдеров

## Архитектура

```
┌─────────────────────────┐
│   AuthManager           │
│  (координатор)          │
└───────────┬─────────────┘
            │
            ├─ EmailAuthenticator
            ├─ PhoneAuthenticator
            └─ SocialAuthenticators[] ← SocialAuthenticatorFactory
                    │
                    ├─ GoogleAuthenticator
                    ├─ YandexAuthenticator
                    └─ VKAuthenticator
                            │
                     ┌──────┴──────┐
                     │ SocialManager│
                     └──────┬──────┘
                            │
                     BitrixSocialServiceAdapter
                            │
                     CSocServAuth (Bitrix)
```

## Настройка Bitrix Social Services

### 1. Активация модуля socialservices

В админке: **Настройки → Настройки продукта → Модули** → установите `socialservices`.

### 2. Настройка провайдеров

**Настройки → Интеграция с соцсетями → Авторизация через соцсети**

Для каждого провайдера:
- Получите API ключи в консоли разработчика (Google, Yandex, VK и т.д.)
- Укажите Client ID и Client Secret
- Настройте Redirect URI

### 3. Регистрация в модуле

Активные в Bitrix соцсети автоматически доступны в модуле beeralex.user.
Модуль автоматически обнаруживает и подключает все включенные в Bitrix социальные сервисы.

Дополнительной настройки не требуется.

## SocialManager

Менеджер для управления социальными адаптерами.

### Внедрение зависимостей

```php
use Beeralex\User\Auth\Social\SocialManager;

$socialManager = service(SocialManager::class);
```

### Получение адаптера

```php
// По ключу
$googleAdapter = $socialManager->get('GoogleOAuth');
$yandexAdapter = $socialManager->get('YandexOAuth');

// Все адаптеры
$adapters = $socialManager->adapters;
foreach ($adapters as $key => $adapter) {
    if ($adapter->isEnable) {
        echo "{$adapter->getName()}: включен\n";
    }
}
```

## BitrixSocialServiceAdapter

Адаптер для работы с Bitrix Social Services.

### Методы

#### `getKey(): string`

Возвращает ключ сервиса.

```php
$adapter = $socialManager->get('GoogleOAuth');
echo $adapter->getKey(); // GoogleOAuth
```

#### `getName(): string`

Возвращает название сервиса.

```php
echo $adapter->getName(); // Google
```

#### `getAuthorizationUrlOrHtml(array $params = []): array`

Возвращает URL или HTML для авторизации.

```php
$result = $adapter->getAuthorizationUrlOrHtml();

if ($result['type'] === 'url') {
    // Redirect на OAuth
    LocalRedirect($result['value']);
} elseif ($result['type'] === 'html') {
    // Вставить HTML форму
    echo $result['value'];
}
```

#### `isAuthorized(): bool`

Проверяет, авторизован ли пользователь.

```php
if ($adapter->isAuthorized()) {
    echo "Пользователь авторизован через Google";
}
```

#### `getProfile(): ?array`

Возвращает профиль пользователя от соцсети.

```php
$profile = $adapter->getProfile();

if ($profile) {
    echo "ID: {$profile['id']}\n";
    echo "Email: {$profile['email']}\n";
    echo "Имя: {$profile['first_name']}\n";
    echo "Фамилия: {$profile['last_name']}\n";
}
```

#### `authorize(): bool`

Запускает процесс авторизации (Bitrix создаёт/логинит пользователя).

```php
if ($adapter->authorize()) {
    echo "Авторизация успешна";
}
```

## SocialServiceAuthenticator

Аутентификатор для социальных сервисов (реализует AuthenticatorContract).

### Создание через фабрику

```php
use Beeralex\User\Auth\SocialAuthenticatorFactory;

$factory = service(SocialAuthenticatorFactory::class);
$authenticators = $factory->makeAll();

foreach ($authenticators as $key => $authenticator) {
    echo "{$authenticator->getTitle()}: {$key}\n";
}
```

### Методы AuthenticatorContract

```php
$authenticator = $authenticators['GoogleOAuth'];

// Ключ
echo $authenticator->getKey(); // GoogleOAuth

// Название
echo $authenticator->getTitle(); // Google

// Проверка типа
if ($authenticator->isService()) {
    echo "Это социальный сервис";
}

// URL авторизации
$urlOrHtml = $authenticator->getAuthorizationUrlOrHtml();
```

### Интеграция с AuthManager

```php
use Beeralex\User\Auth\AuthManager;

$authManager = service(AuthManager::class);

// Получить доступные соцсети
$available = $authManager->getAvailable();

foreach ($available as $key => $authenticator) {
    if ($authenticator->isService()) {
        echo "{$authenticator->getTitle()}\n";
    }
}
```

## REST API

### Контроллер социальной авторизации

```php
use Beeralex\Core\Http\Controllers\ApiController;
use Beeralex\User\Auth\Social\SocialManager;

class SocialAuthController extends ApiController
{
    protected SocialManager $socialManager;
    
    public function __construct()
    {
        parent::__construct();
        $this->socialManager = service(SocialManager::class);
    }
    
    /**
     * GET /api/v1/social/providers/
     * Получить список доступных провайдеров
     */
    public function providersAction()
    {
        $providers = [];
        
        foreach ($this->socialManager->adapters as $key => $adapter) {
            if (!$adapter->isEnable) continue;
            
            $providers[] = [
                'key' => $adapter->getKey(),
                'name' => $adapter->getName(),
            ];
        }
        
        return ['providers' => $providers];
    }
    
    /**
     * GET /api/v1/social/auth/{provider}/
     * Получить URL для авторизации
     */
    public function authAction()
    {
        $provider = $this->request->get('provider');
        
        try {
            $adapter = $this->socialManager->get($provider);
        } catch (\InvalidArgumentException $e) {
            return $this->error('Provider not found', 404);
        }
        
        $result = $adapter->getAuthorizationUrlOrHtml();
        
        return $result;
    }
    
    /**
     * GET /api/v1/social/callback/{provider}/
     * Callback после авторизации
     */
    public function callbackAction()
    {
        $provider = $this->request->get('provider');
        
        try {
            $adapter = $this->socialManager->get($provider);
        } catch (\InvalidArgumentException $e) {
            return $this->error('Provider not found', 404);
        }
        
        // Bitrix обработает callback автоматически
        $success = $adapter->authorize();
        
        if (!$success) {
            return $this->error('Authorization failed');
        }
        
        $profile = $adapter->getProfile();
        
        return [
            'success' => true,
            'profile' => $profile
        ];
    }
}
```

### JavaScript клиент

```javascript
class SocialAuth {
    /**
     * Получить список провайдеров
     */
    async getProviders() {
        const response = await fetch('/api/v1/social/providers/');
        const data = await response.json();
        return data.data.providers;
    }
    
    /**
     * Авторизация через провайдера
     */
    async login(providerKey) {
        const response = await fetch(`/api/v1/social/auth/${providerKey}/`);
        const data = await response.json();
        
        if (data.status === 'success') {
            const result = data.data;
            
            if (result.type === 'url') {
                // Redirect на OAuth
                window.location.href = result.value;
            } else if (result.type === 'html') {
                // Вставить HTML форму
                document.body.insertAdjacentHTML('beforeend', result.value);
            }
        }
    }
}

// Использование
const socialAuth = new SocialAuth();

// Получить кнопки провайдеров
const providers = await socialAuth.getProviders();

providers.forEach(provider => {
    const button = document.createElement('button');
    button.textContent = `Войти через ${provider.name}`;
    button.onclick = () => socialAuth.login(provider.key);
    document.body.appendChild(button);
});
```

## Примеры использования

### Форма авторизации с соцсетями

```php
use Beeralex\User\Auth\AuthManager;

$authManager = service(AuthManager::class);
$authenticators = $authManager->getAvailable();
?>

<h2>Войти через социальные сети</h2>

<div class="social-buttons">
    <?php foreach ($authenticators as $key => $authenticator): ?>
        <?php if (!$authenticator->isService()) continue; ?>
        
        <button 
            class="btn btn-social" 
            data-provider="<?= htmlspecialchars($key) ?>"
        >
            <?= htmlspecialchars($authenticator->getTitle()) ?>
        </button>
    <?php endforeach; ?>
</div>

<script>
document.querySelectorAll('.btn-social').forEach(button => {
    button.addEventListener('click', async function() {
        const provider = this.dataset.provider;
        
        const response = await fetch(`/api/v1/social/auth/${provider}/`);
        const data = await response.json();
        
        if (data.status === 'success' && data.data.type === 'url') {
            window.location.href = data.data.value;
        }
    });
});
</script>
```

### Компонент обратного вызова

```php
class SocialCallbackComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        // Получаем провайдера из URL
        $provider = $this->request->get('provider');
        
        if (!$provider) {
            ShowError('Provider not specified');
            return;
        }
        
        try {
            $socialManager = service(SocialManager::class);
            $adapter = $socialManager->get($provider);
            
            // Bitrix обработает callback
            $success = $adapter->authorize();
            
            if ($success) {
                $profile = $adapter->getProfile();
                $this->arResult['PROFILE'] = $profile;
                $this->arResult['SUCCESS'] = true;
            } else {
                $this->arResult['ERROR'] = 'Authorization failed';
            }
            
        } catch (\Exception $e) {
            $this->arResult['ERROR'] = $e->getMessage();
        }
        
        $this->includeComponentTemplate();
    }
}
```

**Шаблон:**

```php
<?php if ($arResult['SUCCESS']): ?>
    <h2>Успешная авторизация</h2>
    
    <p>Добро пожаловать, <?= htmlspecialchars($arResult['PROFILE']['first_name']) ?>!</p>
    
    <script>
    // Закрыть popup и обновить родительскую страницу
    if (window.opener) {
        window.opener.location.reload();
        window.close();
    } else {
        window.location.href = '/';
    }
    </script>
    
<?php else: ?>
    <h2>Ошибка авторизации</h2>
    <p><?= htmlspecialchars($arResult['ERROR']) ?></p>
<?php endif; ?>
```

### Popup окно для OAuth

```javascript
function openSocialAuth(providerKey) {
    const width = 600;
    const height = 700;
    const left = (screen.width - width) / 2;
    const top = (screen.height - height) / 2;
    
    const popup = window.open(
        `/api/v1/social/auth/${providerKey}/`,
        'SocialAuth',
        `width=${width},height=${height},left=${left},top=${top},resizable=yes,scrollbars=yes`
    );
    
    // Проверка закрытия popup
    const checkPopup = setInterval(() => {
        if (popup.closed) {
            clearInterval(checkPopup);
            
            // Проверить авторизацию
            checkAuthStatus();
        }
    }, 500);
}

async function checkAuthStatus() {
    const response = await fetch('/api/v1/user/current/');
    const data = await response.json();
    
    if (data.data.isAuthorized) {
        console.log('Пользователь авторизован');
        window.location.reload();
    }
}
```

## Создание custom провайдера

### 1. Реализация контракта

```php
use Beeralex\User\Auth\Social\Contracts\SocialServiceProviderContract;

class TelegramProvider implements SocialServiceProviderContract
{
    protected array $config;
    
    public function __construct(array $config)
    {
        $this->config = $config;
    }
    
    public function getKey(): string
    {
        return 'Telegram';
    }
    
    public function getName(): string
    {
        return 'Telegram';
    }
    
    public function getAuthorizationUrlOrHtml(array $params = []): array
    {
        // Telegram Widget
        $botName = $this->config['bot_name'];
        
        $html = <<<HTML
        <script async src="https://telegram.org/js/telegram-widget.js?22"
            data-telegram-login="{$botName}"
            data-size="large"
            data-onauth="onTelegramAuth(user)"
            data-request-access="write">
        </script>
        HTML;
        
        return [
            'type' => 'html',
            'value' => $html
        ];
    }
    
    public function isAuthorized(): bool
    {
        return isset($_SESSION['telegram_user']);
    }
    
    public function getProfile(): ?array
    {
        return $_SESSION['telegram_user'] ?? null;
    }
    
    public function authorize(): bool
    {
        // Проверка hash от Telegram
        $data = $_GET;
        $checkHash = $data['hash'];
        unset($data['hash']);
        
        $dataCheckArr = [];
        foreach ($data as $key => $value) {
            $dataCheckArr[] = $key . '=' . $value;
        }
        sort($dataCheckArr);
        
        $dataCheckString = implode("\n", $dataCheckArr);
        $secretKey = hash('sha256', $this->config['bot_token'], true);
        $hash = hash_hmac('sha256', $dataCheckString, $secretKey);
        
        if (strcmp($hash, $checkHash) !== 0) {
            return false;
        }
        
        // Создать или найти пользователя
        $telegramId = $data['id'];
        $user = $this->findOrCreateUser($telegramId, $data);
        
        if ($user) {
            $_SESSION['telegram_user'] = $data;
            $GLOBALS['USER']->Authorize($user->getId());
            return true;
        }
        
        return false;
    }
    
    protected function findOrCreateUser(int $telegramId, array $data): ?User
    {
        // Поиск по UF_TELEGRAM_ID
        // Создание нового пользователя если не найден
        // ...
    }
}
```

### 2. Регистрация в DI

```php
// .settings.php
'TelegramProvider' => [
    'className' => TelegramProvider::class,
    'constructorParams' => static function() {
        return [
            [
                'bot_name' => 'your_bot_name',
                'bot_token' => 'your_bot_token'
            ]
        ];
    }
],

'SocialManager' => [
    'className' => SocialManager::class,
    'constructorParams' => static function() {
        $adapters = [];
        
        // Bitrix провайдеры
        // ...
        
        // Custom Telegram
        $adapters['Telegram'] = service(TelegramProvider::class);
        
        return [$adapters];
    }
]
```

### 3. Использование

```php
$socialManager = service(SocialManager::class);
$telegram = $socialManager->get('Telegram');

$html = $telegram->getAuthorizationUrlOrHtml();
echo $html['value']; // Telegram widget
```

## Отладка

### Проверка активных провайдеров

```php
$socialManager = service(SocialManager::class);

foreach ($socialManager->adapters as $key => $adapter) {
    echo "{$key}: ";
    echo $adapter->isEnable ? 'enabled' : 'disabled';
    echo "\n";
}
```

### Тестирование авторизации

```php
$adapter = $socialManager->get('GoogleOAuth');

// URL авторизации
$result = $adapter->getAuthorizationUrlOrHtml();
echo "Auth URL: {$result['value']}\n";

// После callback
if ($adapter->isAuthorized()) {
    $profile = $adapter->getProfile();
    print_r($profile);
}
```

## Навигация

- [← Работа с телефонами](phone.md)
- [Расширение модуля →](extending.md)
