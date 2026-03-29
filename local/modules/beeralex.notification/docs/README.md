# Документация модуля beeralex.notification

## Оглавление

1. [Обзор](#обзор)
2. [Архитектура](#архитектура)
3. [Установка](#установка)
4. [Базовые концепции](#базовые-концепции)
5. [NotificationManager](#notificationmanager)
6. [Каналы доставки](#каналы-доставки)
7. [Пользовательские предпочтения](#пользовательские-предпочтения)
8. [Типы уведомлений](#типы-уведомлений)
9. [Перехватчики событий](#перехватчики-событий)
10. [Расширение функционала](#расширение-функционала)
11. [REST API](#rest-api)
12. [Примеры использования](#примеры-использования)

---

## Обзор

**beeralex.notification** — это модуль для управления уведомлениями в Bitrix с поддержкой:

- 📧 **Множественных каналов доставки**: Email, SMS, Telegram, Push (расширяемо)
- 🎛️ **Пользовательских предпочтений**: управление подписками по каналам и типам уведомлений
- 🔌 **Перехватчиков событий**: автоматическая обработка стандартных событий Bitrix (почта, SMS)
- 🧩 **Расширяемой архитектуры**: легкое добавление новых каналов через event-based систему
- 📊 **Администрирования**: управление типами уведомлений, каналами, шаблонами через админку

### Ключевые особенности

- Универсальный интерфейс для отправки уведомлений через разные каналы
- Автоматический выбор каналов на основе предпочтений пользователя
- Интеграция с системой почтовых событий Bitrix
- Защита от рекурсивных вызовов (NotificationLock)
- Контрактно-ориентированная архитектура (Repository pattern)

---

## Архитектура

### Диаграмма компонентов

```
┌─────────────────────────────────────────────────┐
│          NotificationManager                    │
│  (Основной менеджер отправки уведомлений)       │
└────────────┬───────────────────────────┬────────┘
             │                           │
      ┌──────▼──────┐            ┌──────▼──────────┐
      │ Repositories │            │ ChannelFactory  │
      │              │            │ ChannelRegistry │
      └──────────────┘            └──────┬──────────┘
                                         │
                           ┌─────────────┼─────────────┐
                           │             │             │
                    ┌──────▼─────┐ ┌────▼────┐ ┌─────▼──────┐
                    │EmailChannel│ │SmsChannel│ │TelegramCh..│
                    └────────────┘ └──────────┘ └────────────┘
```

### Основные компоненты

1. **NotificationManager** — центральный менеджер, координирующий отправку
2. **ChannelFactory** — фабрика для создания экземпляров каналов
3. **ChannelRegistry** — реестр доступных каналов с событийным расширением
4. **Channels/** — реализации каналов доставки
5. **Repositories/** — репозитории для работы с данными
6. **Events/** — перехватчики событий Bitrix
7. **Tables/** — ORM-таблицы для хранения данных

---

## Установка

### Требования

- PHP 8.1+
- Bitrix Framework 22.0+
- Модули: `main`, `fileman` (опционально)
- Модуль `beeralex.core` (базовые абстракции)

### Процесс установки

1. Разместите модуль в `/local/modules/beeralex.notification/`
2. Установите через административную панель или CLI
3. Модуль автоматически:
   - Создаст необходимые таблицы в БД
   - Зарегистрирует сервисы в DI-контейнере
   - Подключит перехватчики событий

### Структура таблиц БД

```sql
-- Каналы доставки
beeralex_notification_channels (ID, CODE, NAME, ACTIVE, SORT)

-- Типы уведомлений
beeralex_notification_types (ID, CODE, NAME)

-- Предпочтения пользователей
beeralex_user_notification_preferences (USER_ID, NOTIFICATION_TYPE_ID, CHANNEL_ID, ENABLED)

-- Связь типов с событиями
beeralex_notification_link_event_types (ID, EVENT_NAME, EVENT_TYPE_CODE)

-- Связь шаблонов с типами
beeralex_notification_template_links (ID, TEMPLATE_ID, NOTIFICATION_TYPE_ID)

-- Коды уведомлений
beeralex_notification_codes (ID, CODE, NAME)

-- История отправленных уведомлений
beeralex_notifications (ID, USER_ID, CHANNEL_ID, TYPE_ID, SENT_AT, STATUS)
```

---

## Базовые концепции

### NotificationMessage DTO

Объект-контейнер для передачи данных уведомления:

```php
namespace Beeralex\Notification\Dto;

class NotificationMessage
{
    public function __construct(
        public string $eventName,      // Имя события (например, 'NEW_ORDER')
        public array $fields,          // Поля для шаблона
        public int $userId,            // ID пользователя-получателя
        public ?string $lid = null,    // ID сайта
        public ?string $messageId = null,  // ID почтового шаблона
        public ?array $files = null,   // Вложенные файлы
        public ?string $languageId = null  // Язык сообщения
    ) {}
}
```

### Enum Channel

Перечисление доступных каналов:

```php
namespace Beeralex\Notification\Enum;

enum Channel: string
{
    case EMAIL = 'email';
    case SMS = 'sms';
    case TELEGRAM = 'telegram';
    case PUSH = 'push';
}
```

---

## NotificationManager

Основной класс для отправки уведомлений с автоматическим выбором каналов.

### Принцип работы

1. Получает типы уведомлений, связанные с событием
2. Для каждого типа проверяет активные каналы
3. Для каждого канала проверяет предпочтения пользователя
4. Отправляет уведомление через разрешенные каналы

### Методы

#### `notify(NotificationMessage $message): void`

Основной метод отправки уведомления.

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
// Автоматически отправит через все разрешенные каналы
```

#### `sendToChannel(string $code, NotificationMessage $message): void`

Внутренний метод отправки в конкретный канал (protected).

**Логика:**
1. Создает канал через ChannelFactory
2. Отправляет событие `OnBeforeSendToChannel` (для перехвата/модификации)
3. Вызывает метод `send()` канала
4. Логирует ошибки при неудаче

---

## Каналы доставки

### Интерфейс NotificationChannelContract

Все каналы должны реализовывать:

```php
namespace Beeralex\Notification\Contracts;

interface NotificationChannelContract
{
    /**
     * Отправляет уведомление через канал
     */
    public function send(NotificationMessage $message): \Bitrix\Main\Result;

    /**
     * Возвращает человекопонятное название канала для админки
     */
    public static function getDisplayName(): string;

    /**
     * Возвращает уникальный код канала
     */
    public static function getCode(): string;
}
```

### EmailChannel

Отправляет email-уведомления через стандартную систему почтовых событий Bitrix.

```php
namespace Beeralex\Notification\Channels;

class EmailChannel implements NotificationChannelContract
{
    public function send(NotificationMessage $message): \Bitrix\Main\Result
    {
        return Event::send([
            'EVENT_NAME' => $message->eventName,
            'LID' => $message->lid ?? SITE_ID ?: 's1',
            'C_FIELDS' => $message->fields,
            'MESSAGE_ID' => $message->messageId,
            'FILES' => $message->files,
            'LANGUAGE_ID' => $message->languageId,
        ]);
    }

    public static function getCode(): string
    {
        return Channel::EMAIL->value; // 'email'
    }

    public static function getDisplayName(): string
    {
        return 'Email уведомления';
    }
}
```

**Использование:**

```php
$channel = new EmailChannel();
$result = $channel->send($message);

if (!$result->isSuccess()) {
    echo implode('; ', $result->getErrorMessages());
}
```

### SmsChannel

Отправляет SMS-уведомления через специальный SmsEvent.

```php
namespace Beeralex\Notification\Channels;

class SmsChannel implements NotificationChannelContract
{
    public function send(NotificationMessage $message): \Bitrix\Main\Result
    {
        return (new SmsEvent($message->eventName, $message->fields))->send();
    }

    public static function getCode(): string
    {
        return Channel::SMS->value; // 'sms'
    }

    public static function getDisplayName(): string
    {
        return 'SMS уведомления';
    }
}
```

### TelegramChannel

⚠️ **В разработке** — Заглушка для будущей реализации.

```php
namespace Beeralex\Notification\Channels;

class TelegramChannel implements NotificationChannelContract
{
    public function send(NotificationMessage $message): \Bitrix\Main\Result
    {
        // TODO: Реализовать отправку через Telegram Bot API
        return new \Bitrix\Main\Result();
    }

    public static function getCode(): string
    {
        return Channel::TELEGRAM->value; // 'telegram'
    }

    public static function getDisplayName(): string
    {
        return 'Telegram уведомления';
    }
}
```

---

## ChannelFactory и ChannelRegistry

### ChannelFactory

Фабрика для создания экземпляров каналов по их коду.

```php
use Beeralex\Notification\ChannelFactory;

$channel = ChannelFactory::createChannel('email');
// Вернет экземпляр EmailChannel или null, если канал не найден
```

**Реализация:**

```php
class ChannelFactory
{
    public static function createChannel(string $code): ?NotificationChannelContract
    {
        $channels = ChannelRegistry::getAvailableChannels();
        
        foreach ($channels as $class) {
            if (is_subclass_of($class, NotificationChannelContract::class) 
                && $class::getCode() === $code) {
                return new $class;
            }
        }

        return null;
    }
}
```

### ChannelRegistry

Реестр всех доступных каналов с поддержкой динамического расширения через события.

**Дефолтные каналы:**

```php
class ChannelRegistry
{
    protected static array $defaultChannels = [
        Channels\EmailChannel::class,
        Channels\SmsChannel::class,
        Channels\TelegramChannel::class,
    ];
}
```

**Метод getAvailableChannels():**

Возвращает список всех каналов (дефолтные + добавленные через событие).

```php
$channels = ChannelRegistry::getAvailableChannels();
// ['EmailChannel', 'SmsChannel', 'TelegramChannel', ...]
```

---

## Пользовательские предпочтения

### UserNotificationPreferenceTable

ORM-таблица для хранения предпочтений пользователей.

**Структура:**

```php
class UserNotificationPreferenceTable extends DataManager
{
    // Композитный первичный ключ
    USER_ID (int, PK)
    NOTIFICATION_TYPE_ID (int, PK)
    CHANNEL_ID (int, PK)
    ENABLED (boolean, Y/N, default: Y)
}
```

### UserNotificationPreferenceRepository

Репозиторий для работы с предпочтениями.

#### Основные методы

**getByUser(int $userId): array**

Получить все предпочтения пользователя.

```php
$preferenceRepo = service(UserNotificationPreferenceRepositoryContract::class);
$preferences = $preferenceRepo->getByUser(100);

// [
//   ['USER_ID' => 100, 'NOTIFICATION_TYPE_ID' => 1, 'CHANNEL_ID' => 1, 'ENABLED' => 'Y'],
//   ['USER_ID' => 100, 'NOTIFICATION_TYPE_ID' => 1, 'CHANNEL_ID' => 2, 'ENABLED' => 'N'],
//   ...
// ]
```

**isEnabled(int $userId, int $typeId, int $channelId): bool**

Проверить, включен ли канал для данного типа уведомлений.

```php
$isEnabled = $preferenceRepo->isEnabled(
    userId: 100,
    notificationTypeId: 1, // Например, "Новый заказ"
    channelId: 1           // Email
);

if ($isEnabled) {
    echo "Пользователь разрешил получать уведомления о новых заказах на email";
}
```

**setEnabled(int $userId, int $typeId, int $channelId, bool $enabled): void**

Установить предпочтение пользователя.

```php
// Отключить email-уведомления о новых заказах
$preferenceRepo->setEnabled(
    userId: 100,
    notificationTypeId: 1,
    channelId: 1,
    enabled: false
);

// Включить SMS-уведомления
$preferenceRepo->setEnabled(
    userId: 100,
    notificationTypeId: 1,
    channelId: 2,
    enabled: true
);
```

**getUserPreference(int $userId, int $typeId, int $channelId): ?array**

Получить конкретное предпочтение.

```php
$pref = $preferenceRepo->getUserPreference(100, 1, 1);
// ['USER_ID' => 100, 'NOTIFICATION_TYPE_ID' => 1, 'CHANNEL_ID' => 1, 'ENABLED' => 'Y']
```

---

## Типы уведомлений

### NotificationTypeTable

ORM-таблица для хранения типов уведомлений.

**Структура:**

```php
ID (int, PK, auto)
CODE (string, unique, required)  // Например: 'NEW_ORDER', 'PASSWORD_RESET'
NAME (string, required)          // Человекопонятное название
```

### NotificationTypeRepository

#### Основные методы

**getByCode(string $code): ?array**

Получить тип уведомления по коду.

```php
$typeRepo = service(NotificationTypeRepositoryContract::class);
$type = $typeRepo->getByCode('NEW_ORDER');

// ['ID' => 1, 'CODE' => 'NEW_ORDER', 'NAME' => 'Новый заказ']
```

**getAllTypes(): array**

Получить все типы уведомлений.

```php
$types = $typeRepo->getAllTypes();
// [
//   ['ID' => 1, 'CODE' => 'NEW_ORDER', 'NAME' => 'Новый заказ'],
//   ['ID' => 2, 'CODE' => 'PASSWORD_RESET', 'NAME' => 'Сброс пароля'],
//   ...
// ]
```

**exists(string $code): bool**

Проверить существование типа.

```php
if ($typeRepo->exists('NEW_ORDER')) {
    echo "Тип уведомления 'NEW_ORDER' существует";
}
```

**addIfNotExists(string $code, string $name): int**

Добавить тип, если его нет (идемпотентный метод).

```php
$typeId = $typeRepo->addIfNotExists('NEW_ORDER', 'Новый заказ');
// Вернет ID существующего или созданного типа
```

---

## Перехватчики событий

### MailEventInterceptor

Перехватывает стандартные почтовые события Bitrix и обрабатывает их через NotificationManager.

**Принцип работы:**

1. Регистрируется как обработчик события `OnBeforeEventSend`
2. При отправке почты через `\Bitrix\Main\Mail\Event::send()` перехватывает вызов
3. Создает `NotificationMessage` и передает в `NotificationManager`
4. Блокирует стандартную отправку (возвращает `false`)

**Защита от рекурсии:**

Использует `NotificationLock` для предотвращения зацикливания:

```php
if (NotificationLock::isLocked(Channel::EMAIL->value)) {
    return true; // Пропустить обработку
}

NotificationLock::lock(Channel::EMAIL->value);
try {
    // Обработка уведомления
    $manager->notify($message);
} finally {
    NotificationLock::unlock(Channel::EMAIL->value);
}
```

**Пример кода:**

```php
class MailEventInterceptor
{
    public function handle(
        string $eventName,
        string $lid,
        array $fields,
        ?string $messageId = null,
        ?array $files = null,
        ?string $languageId = null
    ): bool {
        if (!$this->moduleEnable || NotificationLock::isLocked(Channel::EMAIL->value)) {
            return true; // Передать стандартной обработке
        }

        $userId = service(UserRepositoryContract::class)->getCurrentUser()->getId();
        if (!$userId) {
            return true;
        }

        NotificationLock::lock(Channel::EMAIL->value);
        try {
            $message = new NotificationMessage($eventName, $fields, $userId, $lid, $messageId, $files, $languageId);
            $manager = new NotificationManager();
            $manager->notify($message);
        } finally {
            NotificationLock::unlock(Channel::EMAIL->value);
        }

        return false; // Заблокировать стандартную отправку
    }
}
```

### SmsEventInterceptor

Аналогично перехватывает SMS-события Bitrix.

---

## Расширение функционала

### Добавление нового канала доставки

#### Шаг 1: Создать класс канала

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
            // Ваша логика отправки через WhatsApp API
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
        // Получить номер телефона пользователя
        $user = \CUser::GetByID($userId)->Fetch();
        return $user['PERSONAL_PHONE'] ?? '';
    }

    protected function formatMessage(NotificationMessage $message): string
    {
        // Форматирование сообщения для WhatsApp
        return "Уведомление: {$message->eventName}\n" . 
               json_encode($message->fields, JSON_UNESCAPED_UNICODE);
    }
}
```

#### Шаг 2: Зарегистрировать канал через событие

В файле `/local/php_interface/init.php`:

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

#### Шаг 3: Добавить канал в БД через админку

Создать запись в таблице `beeralex_notification_channels`:

```sql
INSERT INTO beeralex_notification_channels (CODE, NAME, ACTIVE, SORT)
VALUES ('whatsapp', 'WhatsApp', 'Y', 400);
```

Или через интерфейс: **Настройки → Уведомления → Каналы доставки → Добавить**.

#### Шаг 4: Расширить Enum (опционально)

```php
namespace Beeralex\Notification\Enum;

enum Channel: string
{
    case EMAIL = 'email';
    case SMS = 'sms';
    case TELEGRAM = 'telegram';
    case PUSH = 'push';
    case WHATSAPP = 'whatsapp'; // Добавить
}
```

### Модификация сообщения перед отправкой

Используйте событие `OnBeforeSendToChannel`:

```php
use Bitrix\Main\EventManager;

EventManager::getInstance()->addEventHandler(
    'beeralex.notification',
    'OnBeforeSendToChannel',
    function($channel, $message) {
        // Изменить сообщение для конкретного канала
        if ($channel->getCode() === 'email') {
            $message->fields['CUSTOM_HEADER'] = 'Важное уведомление';
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

### Создание собственного NotificationManager

Расширьте базовый класс:

```php
namespace App\Notification;

use Beeralex\Notification\NotificationManager as BaseManager;
use Beeralex\Notification\Dto\NotificationMessage;

class NotificationManager extends BaseManager
{
    /**
     * Отправить уведомление с логированием
     */
    public function notifyWithLog(NotificationMessage $message): void
    {
        // Логировать попытку отправки
        \Bitrix\Main\Application::getInstance()
            ->getTaggedCache()
            ->startTagCache('/notifications');
        
        $this->notify($message);
        
        // Логировать результат
        file_put_contents(
            '/local/logs/notifications.log',
            date('Y-m-d H:i:s') . " - Sent {$message->eventName} to user {$message->userId}\n",
            FILE_APPEND
        );
    }

    /**
     * Отправить только по конкретным каналам
     */
    public function notifyViaChannels(NotificationMessage $message, array $channels): void
    {
        foreach ($channels as $channelCode) {
            $this->sendToChannel($channelCode, $message);
        }
    }
}
```

Зарегистрируйте в DI через `/local/.settings_extra.php`:

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

### Добавление валидации полей

```php
namespace App\Notification\Validators;

class NotificationValidator
{
    public static function validate(NotificationMessage $message): bool
    {
        // Обязательные поля для типа NEW_ORDER
        if ($message->eventName === 'NEW_ORDER') {
            $required = ['USER_NAME', 'ORDER_ID', 'ORDER_TOTAL'];
            foreach ($required as $field) {
                if (empty($message->fields[$field])) {
                    throw new \InvalidArgumentException("Field {$field} is required");
                }
            }
        }

        return true;
    }
}

// Использование
class CustomNotificationManager extends NotificationManager
{
    public function notify(NotificationMessage $message): void
    {
        NotificationValidator::validate($message);
        parent::notify($message);
    }
}
```

---

## REST API

⚠️ **В разработке** — Планируется интеграция с `beeralex.api` для управления предпочтениями через фронтенд.

### Планируемые эндпоинты

```
GET    /api/v1/notifications/preferences/          - Получить все предпочтения
POST   /api/v1/notifications/preferences/update/   - Обновить предпочтения
GET    /api/v1/notifications/channels/             - Список доступных каналов
GET    /api/v1/notifications/types/                - Список типов уведомлений
GET    /api/v1/notifications/history/              - История отправленных уведомлений
```

### Пример будущего использования

```javascript
// Получить предпочтения пользователя
fetch('/api/v1/notifications/preferences/')
  .then(res => res.json())
  .then(data => {
    console.log('User preferences:', data);
  });

// Отключить email-уведомления о новых заказах
fetch('/api/v1/notifications/preferences/update/', {
  method: 'POST',
  body: JSON.stringify({
    notificationTypeId: 1,
    channelId: 1,
    enabled: false
  })
});
```

---

## Примеры использования

### Простая отправка уведомления

```php
use Beeralex\Notification\NotificationManager;
use Beeralex\Notification\Dto\NotificationMessage;

$manager = new NotificationManager();

$message = new NotificationMessage(
    eventName: 'NEW_ORDER',
    fields: [
        'USER_NAME' => 'Иван Иванов',
        'ORDER_ID' => 12345,
        'ORDER_TOTAL' => '10 000 руб.',
        'ORDER_DATE' => date('d.m.Y H:i')
    ],
    userId: 100
);

$manager->notify($message);
// Отправит уведомление через все разрешенные каналы пользователя
```

### Отправка с указанием сайта

```php
$message = new NotificationMessage(
    eventName: 'PASSWORD_RESET',
    fields: [
        'USER_NAME' => 'Петр Петров',
        'RESET_LINK' => 'https://example.com/reset/abc123'
    ],
    userId: 200,
    lid: 's1' // Указать конкретный сайт
);

$manager->notify($message);
```

### Отправка с файлами (вложения email)

```php
$message = new NotificationMessage(
    eventName: 'INVOICE_CREATED',
    fields: [
        'USER_NAME' => 'Мария Сидорова',
        'INVOICE_NUMBER' => 'INV-2025-001',
        'TOTAL_AMOUNT' => '25 000 руб.'
    ],
    userId: 300,
    files: [
        [
            'name' => 'invoice.pdf',
            'content' => file_get_contents('/path/to/invoice.pdf'),
            'type' => 'application/pdf'
        ]
    ]
);

$manager->notify($message);
```

### Управление предпочтениями пользователя

```php
use Beeralex\Notification\Contracts\UserNotificationPreferenceRepositoryContract;

$preferenceRepo = service(UserNotificationPreferenceRepositoryContract::class);

// Отключить все email-уведомления для пользователя
$types = service(NotificationTypeRepositoryContract::class)->getAllTypes();
$emailChannelId = 1;

foreach ($types as $type) {
    $preferenceRepo->setEnabled(
        userId: 100,
        notificationTypeId: $type['ID'],
        channelId: $emailChannelId,
        enabled: false
    );
}

echo "Email-уведомления отключены для пользователя 100";
```

### Проверка, разрешено ли пользователю отправлять уведомления

```php
$preferenceRepo = service(UserNotificationPreferenceRepositoryContract::class);
$typeRepo = service(NotificationTypeRepositoryContract::class);
$channelRepo = service(NotificationChannelRepositoryContract::class);

$userId = 100;
$eventName = 'NEW_ORDER';

// Получить тип уведомления по коду
$type = $typeRepo->getByCode($eventName);
if (!$type) {
    die("Тип уведомления '{$eventName}' не найден");
}

// Получить активные каналы
$channels = $channelRepo->getActiveChannels();

foreach ($channels as $channel) {
    $isEnabled = $preferenceRepo->isEnabled($userId, $type['ID'], $channel['ID']);
    echo "Канал {$channel['NAME']}: " . ($isEnabled ? 'включен' : 'выключен') . "\n";
}
```

### Программное создание типа уведомления

```php
$typeRepo = service(NotificationTypeRepositoryContract::class);

// Добавить новый тип уведомления
$typeId = $typeRepo->addIfNotExists(
    code: 'WISHLIST_PRICE_DROP',
    name: 'Снижение цены товара из избранного'
);

echo "Создан тип уведомления с ID: {$typeId}";

// Привязать событие к типу
$linkRepo = service(NotificationLinkEventTypeRepositoryContract::class);
$linkRepo->add([
    'EVENT_NAME' => 'PRICE_DROP',
    'EVENT_TYPE_CODE' => 'WISHLIST_PRICE_DROP'
]);
```

### Отправка через конкретный канал напрямую

```php
use Beeralex\Notification\ChannelFactory;
use Beeralex\Notification\Dto\NotificationMessage;

$channel = ChannelFactory::createChannel('sms');

if ($channel) {
    $message = new NotificationMessage(
        eventName: 'SMS_CODE',
        fields: ['CODE' => '123456'],
        userId: 100
    );

    $result = $channel->send($message);

    if ($result->isSuccess()) {
        echo "SMS отправлена успешно";
    } else {
        echo "Ошибка: " . implode('; ', $result->getErrorMessages());
    }
}
```

### Интеграция с модулем заказов

```php
use Bitrix\Main\EventManager;
use Beeralex\Notification\NotificationManager;
use Beeralex\Notification\Dto\NotificationMessage;

// Подписаться на событие создания заказа
EventManager::getInstance()->addEventHandler(
    'sale',
    'OnOrderAdd',
    function($orderId, $orderFields) {
        $manager = new NotificationManager();

        $message = new NotificationMessage(
            eventName: 'NEW_ORDER',
            fields: [
                'ORDER_ID' => $orderId,
                'USER_NAME' => $orderFields['USER_NAME'] ?? '',
                'ORDER_TOTAL' => $orderFields['PRICE'] ?? 0,
                'ORDER_DATE' => date('d.m.Y H:i'),
            ],
            userId: $orderFields['USER_ID']
        );

        $manager->notify($message);
    }
);
```

### Создание компонента управления предпочтениями

```php
class NotificationPreferencesComponent extends \CBitrixComponent
{
    public function executeComponent()
    {
        global $USER;
        $userId = $USER->GetID();

        $preferenceRepo = service(UserNotificationPreferenceRepositoryContract::class);
        $typeRepo = service(NotificationTypeRepositoryContract::class);
        $channelRepo = service(NotificationChannelRepositoryContract::class);

        // Получить все типы и каналы
        $this->arResult['TYPES'] = $typeRepo->getAllTypes();
        $this->arResult['CHANNELS'] = $channelRepo->getActiveChannels();

        // Получить текущие предпочтения
        $preferences = $preferenceRepo->getByUser($userId);
        $this->arResult['PREFERENCES'] = [];

        foreach ($preferences as $pref) {
            $key = "{$pref['NOTIFICATION_TYPE_ID']}_{$pref['CHANNEL_ID']}";
            $this->arResult['PREFERENCES'][$key] = $pref['ENABLED'] === 'Y';
        }

        // Обработка формы
        if ($this->request->isPost() && check_bitrix_sessid()) {
            foreach ($this->arResult['TYPES'] as $type) {
                foreach ($this->arResult['CHANNELS'] as $channel) {
                    $fieldName = "pref_{$type['ID']}_{$channel['ID']}";
                    $enabled = isset($_POST[$fieldName]);

                    $preferenceRepo->setEnabled(
                        $userId,
                        $type['ID'],
                        $channel['ID'],
                        $enabled
                    );
                }
            }

            LocalRedirect($APPLICATION->GetCurPageParam());
        }

        $this->includeComponentTemplate();
    }
}
```

**Шаблон компонента:**

```php
<form method="post">
    <?= bitrix_sessid_post() ?>
    
    <table class="notification-preferences">
        <thead>
            <tr>
                <th>Тип уведомления</th>
                <?php foreach ($arResult['CHANNELS'] as $channel): ?>
                    <th><?= htmlspecialchars($channel['NAME']) ?></th>
                <?php endforeach; ?>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($arResult['TYPES'] as $type): ?>
                <tr>
                    <td><?= htmlspecialchars($type['NAME']) ?></td>
                    <?php foreach ($arResult['CHANNELS'] as $channel): ?>
                        <td>
                            <?php
                            $key = "{$type['ID']}_{$channel['ID']}";
                            $checked = $arResult['PREFERENCES'][$key] ?? false;
                            ?>
                            <input type="checkbox"
                                   name="pref_<?= $type['ID'] ?>_<?= $channel['ID'] ?>"
                                   <?= $checked ? 'checked' : '' ?>>
                        </td>
                    <?php endforeach; ?>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    
    <button type="submit">Сохранить настройки</button>
</form>
```

---

## Административная часть

Модуль включает админ-панель для управления:

- **Типы уведомлений** (`/bitrix/admin/beeralex_notification_types.php`)
- **Каналы доставки** (`/bitrix/admin/beeralex_notification_channels.php`)
- **Связи событий с типами** (`/bitrix/admin/beeralex_notification_link_events.php`)
- **Связи шаблонов** (`/bitrix/admin/beeralex_notification_template_links.php`)

Меню регистрируется в разделе **Настройки → Уведомления**.

---

## NotificationLock

Механизм защиты от рекурсивных вызовов при обработке событий.

### Методы

```php
class NotificationLock
{
    // Заблокировать канал
    public static function lock(string $channel): void;

    // Разблокировать канал
    public static function unlock(string $channel): void;

    // Проверить, заблокирован ли канал
    public static function isLocked(string $channel): bool;
}
```

### Пример использования

```php
if (NotificationLock::isLocked('email')) {
    return; // Канал уже обрабатывается
}

NotificationLock::lock('email');
try {
    // Отправка уведомления
} finally {
    NotificationLock::unlock('email');
}
```

---

## Лучшие практики

### 1. Всегда используйте try-finally для блокировок

```php
NotificationLock::lock($channelCode);
try {
    // Ваш код
} finally {
    NotificationLock::unlock($channelCode);
}
```

### 2. Обрабатывайте ошибки отправки

```php
$result = $channel->send($message);
if (!$result->isSuccess()) {
    log('Ошибка отправки: ' . implode('; ', $result->getErrorMessages()), 6, true);
}
```

### 3. Валидируйте данные перед отправкой

```php
if (empty($message->fields['USER_EMAIL'])) {
    throw new \InvalidArgumentException('Email пользователя обязателен');
}
```

### 4. Используйте типизированные DTO

```php
// Плохо
$data = ['event' => 'NEW_ORDER', 'fields' => [...], 'user' => 100];

// Хорошо
$message = new NotificationMessage('NEW_ORDER', [...], 100);
```

### 5. Добавляйте каналы через события, а не модификацией кода модуля

```php
// Правильно: через событие
EventManager::getInstance()->addEventHandler(
    'beeralex.notification',
    'OnBuildChannels',
    function() {
        return new EventResult(EventResult::SUCCESS, [MyChannel::class]);
    }
);

// Неправильно: изменять ChannelRegistry::$defaultChannels
```

---

## Отладка и логирование

### Включение логирования

В файле `/local/modules/beeralex.notification/include/functions.php` есть функция `log()`:

```php
function log($message, $level = 6, $force = false)
{
    if ($force || defined('LOG_NOTIFICATION')) {
        \Bitrix\Main\Diag\Debug::writeToFile($message, '', '/local/logs/notification.log');
    }
}
```

Для включения логов добавьте в `/local/php_interface/init.php`:

```php
define('LOG_NOTIFICATION', true);
```

### Просмотр ошибок отправки

Все ошибки логируются автоматически в `/local/logs/notification.log`:

```bash
tail -f /local/logs/notification.log
```

### Отладка через XDebug

Поставьте breakpoint в методе `NotificationManager::notify()` для пошагового анализа.

---

## Дополнительные репозитории

### NotificationChannelRepository

Управление каналами доставки.

**Методы:**
- `getActiveChannels(): array` — получить активные каналы
- `getByCode(string $code): ?array` — получить канал по коду
- `add(array $data): int` — добавить канал
- `update(int $id, array $data): void` — обновить канал

### NotificationLinkEventTypeRepository

Связь событий с типами уведомлений.

**Методы:**
- `getByEventName(string $eventName): array` — получить типы по имени события
- `linkEventToType(string $eventName, string $typeCode): int` — связать событие с типом

### NotificationsRepository

История отправленных уведомлений.

**Методы:**
- `getByUser(int $userId, int $limit = 100): array` — история пользователя
- `add(array $data): int` — добавить запись об отправке
- `getStatistics(int $userId): array` — статистика по каналам

---

## Зависимости

- **beeralex.core** — базовые абстракции (Repository, TableManagerTrait)
- **beeralex.user** (опционально) — для работы с пользователями через UserRepositoryContract
- **Bitrix/Main** — Events, ORM, Result

---

## Лицензия

Проприетарный модуль. © beeralex

---

## Заключение

Модуль **beeralex.notification** предоставляет гибкую и расширяемую систему для управления уведомлениями с поддержкой:

- ✅ Множественных каналов доставки
- ✅ Пользовательских предпочтений
- ✅ Перехвата стандартных событий Bitrix
- ✅ Event-driven архитектуры для расширений
- ✅ Защиты от рекурсии и ошибок

Для добавления новых функций используйте события и наследование классов, избегая модификации кода модуля напрямую.
