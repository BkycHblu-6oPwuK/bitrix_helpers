# Расширения модуля Sale

Модуль `beeralex.catalog` предоставляет расширения для модуля `sale` Bitrix, включая кастомные чеки, ограничения и дополнительные сервисы доставки.

---

## PrepaymentCheck

### Описание

Кастомный тип чека для частичной предоплаты с исправленным округлением. Решает проблему некорректного расчета сумм при частичной оплате заказа в стандартной реализации Bitrix.

### Наследование

```php
class PrepaymentCheck extends \Bitrix\Sale\Cashbox\Check
```

### Тип чека

```php
public static function getType(): string
{
    return 'prepayment'; // Частичная предоплата
}
```

### Основные методы

#### getType()

Возвращает тип чека.

```php
public static function getType(): string
```

#### getCalculatedSign()

Возвращает признак расчета (приход).

```php
public static function getCalculatedSign(): string
{
    return static::CALCULATED_SIGN_INCOME;
}
```

#### getName()

Название чека в административной панели.

```php
public static function getName(): string
{
    return 'Частичная предоплата custom';
}
```

#### getSupportedEntityType()

Тип поддерживаемой сущности (платеж).

```php
public static function getSupportedEntityType(): string
{
    return static::SUPPORTED_ENTITY_TYPE_PAYMENT;
}
```

#### getSupportedRelatedEntityType()

Тип связанной сущности (отгрузка).

```php
public static function getSupportedRelatedEntityType(): string
{
    return static::SUPPORTED_ENTITY_TYPE_SHIPMENT;
}
```

### Особенности работы

#### Исправление округления

Стандартная реализация Bitrix может приводить к расхождениям в копейках при расчете частичной предоплаты. Этот класс решает проблему:

1. Рассчитывает коэффициент предоплаты: `rate = paymentSum / orderPrice`
2. Применяет коэффициент ко всем позициям кроме последней
3. Для последней позиции вычисляет остаток: `lastPrice = paymentSum - sumOfOtherPositions`

**Метод correlatePrices():**

```php
private function correlatePrices(array $result): array
{
    $paymentSum = 0;
    foreach ($result['PAYMENTS'] as $payment) {
        $paymentSum += $payment['SUM'];
    }
    
    $order = $result['ORDER'];
    $rate = $paymentSum / $order->getPrice();
    
    // Рассчитываем для всех позиций кроме последней
    $totalSum = 0;
    for ($i = 0; $i < $countProductPositions - 1; $i++) {
        $totalSum += $this->correlatePosition($result['PRODUCTS'][$i], $rate);
    }
    
    // Для последней позиции берем остаток
    $lastElement = $countProductPositions - 1;
    $result['PRODUCTS'][$lastElement]['SUM'] = $paymentSum - $totalSum;
    // ...
}
```

#### Установка признака платежа

Все позиции и доставка помечаются признаком `PAYMENT_OBJECT_PAYMENT`:

```php
foreach ($result['PRODUCTS'] as $i => $item) {
    $result['PRODUCTS'][$i]['PAYMENT_OBJECT'] = static::PAYMENT_OBJECT_PAYMENT;
}
```

#### Отключение маркировки

Маркировочные коды не печатаются в чеке предоплаты:

```php
protected function needPrintMarkingCode($basketItem): bool
{
    return false;
}
```

### Регистрация

Чек автоматически регистрируется через обработчик события:

```php
// В EventHandlers::onGetCustomCheckList()
return new EventResult(
    EventResult::SUCCESS,
    [
        PrepaymentCheck::class => $filepath,
    ]
);
```

### Использование

После установки модуля чек становится доступен в настройках касс:

1. Перейдите в: **Магазин → Настройки → Кассы**
2. Создайте или отредактируйте кассу
3. В типах чеков выберите **"Частичная предоплата custom"**

---

## UserRestriction

### Описание

Ограничение доступности платежных систем, касс и служб доставки по пользователям. Позволяет ограничить видимость сервисов только для определенных пользователей или администраторов.

### Наследование

```php
class UserRestriction extends \Bitrix\Sale\Services\Base\Restriction
```

### Основные методы

#### check()

Проверяет, доступен ли сервис для текущего пользователя.

```php
public static function check($current, array $restrictionParams, $service = null): bool
```

**Параметры:**
- `$current` - ID текущего пользователя
- `$restrictionParams` - параметры ограничения:
  - `USER_IDS` - массив разрешенных ID пользователей
  - `ALLOW_TEST_USERS` - разрешить тестовым (администраторам)
- `$service` - сервис (не используется)

**Логика работы:**

```php
// 1. Если разрешены тестовые пользователи и текущий - админ
if ($allowTestUsers && $USER->IsAdmin()) {
    return true;
}

// 2. Если пользователь в списке разрешенных
if (in_array($current, $allowedUsers)) {
    return true;
}

// 3. Если список пуст и тестовые отключены - доступно всем
if (empty($allowedUsers) && !$allowTestUsers) {
    return true;
}

return false;
```

#### getParamsStructure()

Определяет структуру параметров для настройки в админке.

```php
public static function getParamsStructure($entityId = 0): array
{
    return [
        'USER_IDS' => [
            'TYPE' => 'STRING',
            'MULTIPLE' => 'Y',
            'LABEL' => 'ID пользователей (ввод вручную)',
        ],
        'ALLOW_TEST_USERS' => [
            'TYPE' => 'Y/N',
            'LABEL' => 'Разрешить тестовых пользователей',
            'DEFAULT' => 'N',
        ],
    ];
}
```

#### extractParams()

Извлекает ID пользователя из сущности (заказа, платежа, отгрузки).

```php
public static function extractParams(Entity $entity): int
```

#### getClassTitle()

Название ограничения в административной панели.

```php
public static function getClassTitle(): string
{
    return 'Ограничение по пользователю';
}
```

#### getClassDescription()

Описание ограничения.

```php
public static function getClassDescription(): string
{
    return 'Ограничивает доступность сервиса для конкретных и/или тестовых пользователей.';
}
```

### Регистрация

Ограничение регистрируется для трех типов сервисов:

```php
// Платежные системы
EventManager::getInstance()->registerEventHandler(
    'sale',
    'onSalePaySystemRestrictionsClassNamesBuildList',
    $this->MODULE_ID,
    EventHandlers::class,
    'onSalePaySystemRestrictionsClassNamesBuildList'
);

// Кассы
EventManager::getInstance()->registerEventHandler(
    'sale',
    'onSaleCashboxRestrictionsClassNamesBuildList',
    $this->MODULE_ID,
    EventHandlers::class,
    'onSaleCashboxRestrictionsClassNamesBuildList'
);
```

### Использование

После установки модуля ограничение становится доступным:

1. **Для платежных систем:**
   - Магазин → Настройки → Платежные системы → [Система] → Ограничения
   - Добавьте "Ограничение по пользователю"
   - Укажите ID пользователей или включите тестовых

2. **Для касс:**
   - Магазин → Настройки → Кассы → [Касса] → Ограничения
   - Добавьте "Ограничение по пользователю"

**Примеры настройки:**

```
Сценарий 1: Только для админов
- USER_IDS: пусто
- ALLOW_TEST_USERS: Y

Сценарий 2: Только для конкретных пользователей
- USER_IDS: 1, 5, 10
- ALLOW_TEST_USERS: N

Сценарий 3: Для админов + конкретных пользователей
- USER_IDS: 5, 10
- ALLOW_TEST_USERS: Y

Сценарий 4: Для всех (без ограничений)
- USER_IDS: пусто
- ALLOW_TEST_USERS: N
```

---

## MyPriceExtraService

### Описание

Дополнительный сервис доставки для установки фиксированной цены. Позволяет добавить к стоимости доставки произвольную сумму (например, за подъем на этаж, упаковку и т.д.).

### Наследование

```php
class MyPriceExtraService extends \Bitrix\Sale\Delivery\ExtraServices\Base
```

### Конструктор

```php
public function __construct(
    $id,
    array $initParams,
    $currency,
    $value = null,
    array $additionalParams = array()
)
```

Устанавливает тип параметра как `STRING` для ввода произвольной цены.

### Основные методы

#### getClassTitle()

Название сервиса в административной панели.

```php
public static function getClassTitle(): string
{
    return 'Сервис для установки определенной цены в доставку';
}
```

#### getCostShipment()

Возвращает стоимость сервиса.

```php
public function getCostShipment(?Shipment $shipment = null): float
{
    return $this->convertToOperatingCurrency((float)$this->value);
}
```

#### getPriceShipment()

Возвращает цену сервиса (аналогично `getCostShipment`).

```php
public function getPriceShipment(?Shipment $shipment = null): float
{
    return $this->convertToOperatingCurrency((float)$this->value);
}
```

### Регистрация

Регистрируется через обработчик события:

```php
EventManager::getInstance()->registerEventHandler(
    'sale',
    'onSaleDeliveryExtraServicesClassNamesBuildList',
    $this->MODULE_ID,
    EventHandlers::class,
    'onSaleDeliveryExtraServicesClassNamesBuildList'
);
```

### Использование

После установки модуля сервис доступен:

1. Перейдите: **Магазин → Настройки → Службы доставки**
2. Создайте или отредактируйте службу доставки
3. Перейдите на вкладку **"Дополнительные услуги"**
4. Добавьте **"Сервис для установки определенной цены в доставку"**
5. Укажите цену сервиса

**Пример:**

```
Служба доставки: Курьерская доставка
Базовая стоимость: 300 руб.

Дополнительные услуги:
- Подъем на этаж: 50 руб. (MyPriceExtraService)
- Упаковка: 100 руб. (MyPriceExtraService)

Итого с услугами: 450 руб.
```

---

## Интеграция расширений

### Проверка доступности в коде

```php
use Beeralex\Catalog\Restriction\UserRestriction;

$userId = $USER->GetID();
$restrictionParams = [
    'USER_IDS' => [1, 5, 10],
    'ALLOW_TEST_USERS' => 'Y'
];

if (UserRestriction::check($userId, $restrictionParams)) {
    echo "Сервис доступен";
} else {
    echo "Сервис недоступен";
}
```

### Работа с чеком предоплаты

```php
use Beeralex\Catalog\Cashbox\PrepaymentCheck;

// Чек создается автоматически при создании платежа
// через систему касс Bitrix

// Проверка типа чека
$checkType = PrepaymentCheck::getType();
// 'prepayment'

$checkName = PrepaymentCheck::getName();
// 'Частичная предоплата custom'
```

### Добавление дополнительного сервиса доставки программно

```php
$delivery = \Bitrix\Sale\Delivery\Services\Manager::getById($deliveryId);
if ($delivery) {
    $extraService = [
        'CODE' => 'FLOOR_LIFT',
        'NAME' => 'Подъем на этаж',
        'CLASS_NAME' => \Beeralex\Catalog\ExtraService\MyPriceExtraService::class,
        'INIT_VALUE' => 50.0, // Цена
        'ACTIVE' => 'Y',
    ];
    
    // Добавление через админку или API
}
```

---

## Отладка и тестирование

### Логирование чеков

```php
// В PrepaymentCheck можно добавить логирование
protected function extractDataInternal(): array
{
    $result = parent::extractDataInternal();
    
    // Логирование для отладки
    \Beeralex\Catalog\log("PrepaymentCheck data: " . print_r($result, true));
    
    $result = $this->correlatePrices($result);
    return $result;
}
```

### Тестирование ограничений

```php
// test_restriction.php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Beeralex\Catalog\Restriction\UserRestriction;

global $USER;

$tests = [
    ['USER_IDS' => [], 'ALLOW_TEST_USERS' => 'N'],      // Доступно всем
    ['USER_IDS' => [1, 2], 'ALLOW_TEST_USERS' => 'N'],  // Только для 1 и 2
    ['USER_IDS' => [], 'ALLOW_TEST_USERS' => 'Y'],      // Только для админов
];

foreach ($tests as $i => $params) {
    $result = UserRestriction::check($USER->GetID(), $params);
    echo "Test " . ($i + 1) . ": " . ($result ? "PASSED" : "FAILED") . "\n";
}
```

---

## Рекомендации

1. **Используйте PrepaymentCheck** если у вас есть проблемы с округлением в стандартных чеках предоплаты
2. **Ограничивайте сервисы** через UserRestriction для тестирования новых платежных систем
3. **Создавайте кастомные ExtraService** для специфичных услуг доставки
4. **Логируйте работу чеков** для отладки проблем с кассами
5. **Тестируйте на тестовых заказах** перед внедрением в продакшн

---

## Расширение функциональности

Вы можете создать свои расширения аналогично:

```php
namespace App\Sale;

use Bitrix\Sale\Services\Base\Restriction;

class RegionRestriction extends Restriction
{
    public static function check($region, array $restrictionParams, $service = null): bool
    {
        $allowedRegions = $restrictionParams['REGIONS'] ?? [];
        return empty($allowedRegions) || in_array($region, $allowedRegions);
    }
    
    public static function getParamsStructure($entityId = 0): array
    {
        return [
            'REGIONS' => [
                'TYPE' => 'STRING',
                'MULTIPLE' => 'Y',
                'LABEL' => 'Коды регионов',
            ]
        ];
    }
    
    // ... остальные методы
}
```

Затем зарегистрируйте в обработчике события модуля.
