# Модуль beeralex.notification

Универсальная система управления уведомлениями для Bitrix с поддержкой множественных каналов доставки и пользовательских предпочтений. Расширяет стандартные возможности Bitrix для email и SMS до любых других каналов (Telegram, Push, мессенджеры).

## Основные возможности

- 📧 **Множественные каналы**: Email, SMS, Telegram, Push (легко расширяется)
- 🎛️ **Управление предпочтениями**: пользователи выбирают каналы для каждого типа уведомлений
- 🔌 **Перехват событий**: автоматическая обработка стандартных почтовых и SMS событий Bitrix
- 🧩 **Расширяемость**: добавление новых каналов через event-based систему
- 🛡️ **Защита от рекурсии**: NotificationLock предотвращает зацикливание
- 📊 **Администрирование**: управление типами, каналами, шаблонами через админку

## Статус разработки

⚠️ **Модуль в разработке**. Реализовано:
- ✅ Архитектура и DI-контейнер
- ✅ Email и SMS каналы
- ✅ Система предпочтений пользователей
- ✅ Перехватчики событий
- ✅ Административная панель

Планируется:
- 🚧 Telegram канал (заглушка создана)
- 🚧 REST API для управления предпочтениями
- 🚧 История отправленных уведомлений
- 🚧 Push-уведомления

## Требования

- PHP 8.1+
- Bitrix Framework 22.0+
- Модуль `beeralex.core` (базовые абстракции)
- Модули Bitrix: `main`, `fileman`

## Установка

1. Разместите модуль в `/local/modules/beeralex.notification/`
2. Установите через административную панель
3. Модуль автоматически создаст таблицы и зарегистрирует сервисы

## Быстрый старт

### Отправка уведомления

```php
use Beeralex\Notification\NotificationManager;
use Beeralex\Notification\Dto\NotificationMessage;

$manager = new NotificationManager();

$message = new NotificationMessage(
    eventName: 'NEW_ORDER',
    fields: [
        'USER_NAME' => 'Иван Петров',
        'ORDER_ID' => 12345,
        'ORDER_TOTAL' => '5000 руб.'
    ],
    userId: 100
);

$manager->notify($message);
// Автоматически отправит через все разрешенные каналы пользователя
```

### Управление предпочтениями

```php
use Beeralex\Notification\Contracts\UserNotificationPreferenceRepositoryContract;

$preferenceRepo = service(UserNotificationPreferenceRepositoryContract::class);

// Проверить, включен ли канал
$isEnabled = $preferenceRepo->isEnabled(
    userId: 100,
    notificationTypeId: 1,  // Тип: "Новый заказ"
    channelId: 1            // Канал: Email
);

// Изменить предпочтение
$preferenceRepo->setEnabled(
    userId: 100,
    notificationTypeId: 1,
    channelId: 2,  // SMS
    enabled: true
);
```

### Отправка через конкретный канал

```php
use Beeralex\Notification\ChannelFactory;

$channel = ChannelFactory::createChannel('email');
$result = $channel->send($message);

if (!$result->isSuccess()) {
    echo implode('; ', $result->getErrorMessages());
}
```

## Архитектура

### Основные компоненты

```
NotificationManager          → Координирует отправку
├── ChannelFactory           → Создает экземпляры каналов
├── ChannelRegistry          → Реестр доступных каналов
├── Repositories             → Работа с БД (типы, предпочтения, каналы)
└── Channels/
    ├── EmailChannel         → Отправка через Bitrix Mail Events
    ├── SmsChannel           → SMS через провайдеров
    └── TelegramChannel      → В разработке
```

### Таблицы БД

- `beeralex_notification_channels` — доступные каналы доставки
- `beeralex_notification_types` — типы уведомлений (NEW_ORDER, PASSWORD_RESET и т.д.)
- `beeralex_user_notification_preferences` — предпочтения пользователей
- `beeralex_notification_link_event_types` — связь событий с типами
- `beeralex_notification_template_links` — связь шаблонов с типами
- `beeralex_notifications` — история отправки

## Расширение функционала

### Добавление нового канала доставки

#### 1. Создать класс канала

```php
namespace App\Notification\Channels;

use Beeralex\Notification\Contracts\NotificationChannelContract;
use Beeralex\Notification\Dto\NotificationMessage;

class WhatsAppChannel implements NotificationChannelContract
{
    public function send(NotificationMessage $message): \Bitrix\Main\Result
    {
        $result = new \Bitrix\Main\Result();
        
        try {
            // Логика отправки через WhatsApp API
            $api = new WhatsAppApi();
            $api->sendMessage(
                phone: $this->getUserPhone($message->userId),
                text: $this->formatMessage($message)
            );
        } catch (\Exception $e) {
            $result->addError(new \Bitrix\Main\Error($e->getMessage()));
        }
        
        return $result;
    }

    public static function getCode(): string
    {
        return 'whatsapp';
    }

    public static function getDisplayName(): string
    {
        return 'WhatsApp уведомления';
    }

    protected function getUserPhone(int $userId): string
    {
        $user = \CUser::GetByID($userId)->Fetch();
        return $user['PERSONAL_PHONE'] ?? '';
    }

    protected function formatMessage(NotificationMessage $message): string
    {
        return "Уведомление: {$message->eventName}\n" . 
               json_encode($message->fields, JSON_UNESCAPED_UNICODE);
    }
}
```

#### 2. Зарегистрировать через событие

В `/local/php_interface/init.php`:

```php
use Bitrix\Main\EventManager;

EventManager::getInstance()->addEventHandler(
    'beeralex.notification',
    'OnBuildChannels',
    function() {
        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            [
                \App\Notification\Channels\WhatsAppChannel::class
            ]
        );
    }
);
```

#### 3. Добавить в БД

Через админку или SQL:

```sql
INSERT INTO beeralex_notification_channels (CODE, NAME, ACTIVE, SORT)
VALUES ('whatsapp', 'WhatsApp', 'Y', 400);
```

### Модификация сообщений перед отправкой

Используйте событие `OnBeforeSendToChannel`:

```php
EventManager::getInstance()->addEventHandler(
    'beeralex.notification',
    'OnBeforeSendToChannel',
    function($channel, $message) {
        // Добавить кастомные поля
        if ($channel->getCode() === 'email') {
            $message->fields['CUSTOM_HEADER'] = 'Важное';
        }

        return new \Bitrix\Main\EventResult(
            \Bitrix\Main\EventResult::SUCCESS,
            [
                'channel' => $channel,
                'message' => $message,
            ]
        );
    }
);
```

### Создание собственного менеджера

```php
namespace App\Notification;

use Beeralex\Notification\NotificationManager as BaseManager;
use Beeralex\Notification\Dto\NotificationMessage;

class NotificationManager extends BaseManager
{
    /**
     * Отправить только по указанным каналам
     */
    public function notifyViaChannels(NotificationMessage $message, array $channels): void
    {
        foreach ($channels as $channelCode) {
            $this->sendToChannel($channelCode, $message);
        }
    }

    /**
     * Отправить с логированием
     */
    public function notifyWithLog(NotificationMessage $message): void
    {
        $this->notify($message);
        
        file_put_contents(
            '/local/logs/notifications.log',
            date('Y-m-d H:i:s') . " - Sent {$message->eventName} to user {$message->userId}\n",
            FILE_APPEND
        );
    }
}
```

Зарегистрируйте в `/local/.settings_extra.php`:

```php
use Beeralex\Notification\NotificationManager;
use App\Notification\NotificationManager as AppManager;

return [
    'services' => [
        'value' => [
            NotificationManager::class => [
                'constructor' => static function () {
                    return new AppManager();
                }
            ],
        ]
    ]
];
```

## Каналы доставки

### EmailChannel

Интегрируется с почтовыми событиями Bitrix через `\Bitrix\Main\Mail\Event::send()`.

```php
$channel = new EmailChannel();
$result = $channel->send($message);
```

### SmsChannel

Отправляет SMS через `SmsEvent` (требует настройки провайдера SMS).

```php
$channel = new SmsChannel();
$result = $channel->send($message);
```

### TelegramChannel

⚠️ **В разработке**. Возвращает пустой Result (заглушка).

## Перехватчики событий

### MailEventInterceptor

Перехватывает стандартные почтовые события Bitrix (`OnBeforeEventSend`) и обрабатывает их через NotificationManager.

**Защита от рекурсии:**
Использует `NotificationLock` для предотвращения повторной обработки.

```php
// Автоматически регистрируется при установке модуля
// Перехватывает все вызовы \Bitrix\Main\Mail\Event::send()
```

### SmsEventInterceptor

Аналогично для SMS-событий.

## Типы уведомлений

Управляйте через репозиторий:

```php
use Beeralex\Notification\Contracts\NotificationTypeRepositoryContract;

$typeRepo = service(NotificationTypeRepositoryContract::class);

// Добавить тип
$typeId = $typeRepo->addIfNotExists('NEW_ORDER', 'Новый заказ');

// Проверить существование
if ($typeRepo->exists('PASSWORD_RESET')) {
    echo "Тип существует";
}

// Получить все типы
$types = $typeRepo->getAllTypes();
```

## Административная панель

Доступна в разделе **Настройки → Уведомления**:

- Управление типами уведомлений
- Настройка каналов доставки
- Связь событий с типами
- Связь шаблонов с типами

## Примеры интеграции

### С модулем заказов

```php
use Bitrix\Main\EventManager;

EventManager::getInstance()->addEventHandler(
    'sale',
    'OnOrderAdd',
    function($orderId, $fields) {
        $manager = new NotificationManager();
        
        $message = new NotificationMessage(
            eventName: 'NEW_ORDER',
            fields: [
                'ORDER_ID' => $orderId,
                'USER_NAME' => $fields['USER_NAME'],
                'TOTAL' => $fields['PRICE']
            ],
            userId: $fields['USER_ID']
        );
        
        $manager->notify($message);
    }
);
```

### Компонент настроек предпочтений

Создайте компонент для личного кабинета:

```php
class NotificationPreferencesComponent extends \CBitrixComponent
{
    public function executeComponent()
    {
        $userId = $GLOBALS['USER']->GetID();
        
        $preferenceRepo = service(UserNotificationPreferenceRepositoryContract::class);
        $this->arResult['PREFERENCES'] = $preferenceRepo->getByUser($userId);
        
        // Обработка формы изменения настроек
        if ($this->request->isPost()) {
            // Сохранение предпочтений
        }
        
        $this->includeComponentTemplate();
    }
}
```

## Отладка

Включите логирование в `/local/php_interface/init.php`:

```php
define('LOG_NOTIFICATION', true);
```

Логи сохраняются в `/local/logs/notification.log`.

## REST API (планируется)

```
GET    /api/v1/notifications/preferences/          - Получить предпочтения
POST   /api/v1/notifications/preferences/update/   - Обновить предпочтения
GET    /api/v1/notifications/channels/             - Список каналов
GET    /api/v1/notifications/types/                - Список типов
```

## Зависимости

- `beeralex.core` — базовые абстракции (Repository, TableManagerTrait)
- `beeralex.user` (опционально) — для UserRepositoryContract
- Bitrix/Main — Events, ORM, Result

## Документация

Полная документация доступна в [docs/README.md](./docs/README.md)

## Лицензия

Проприетарный модуль. © beeralex

