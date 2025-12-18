# Корзина и скидки

Модуль `beeralex.catalog` предоставляет мощную систему управления корзиной покупателя и применения скидок. Система построена на паттерне Service Layer с использованием фабрик для создания экземпляров сервисов.

---

## Архитектура

```
BasketFactory → BasketService → Basket (Bitrix)
                     ↓
              DiscountService
                     ↓
              CouponsService
```

---

## BasketService

### Описание

Главный сервис для работы с корзиной. Предоставляет методы для добавления, удаления, изменения количества товаров, а также для применения скидок и купонов.

### Конструктор

```php
public function __construct(
    protected readonly BasketBase $basket,
    protected readonly BasketUtils $basketUtils,
    protected readonly CouponsService $couponsService,
    protected readonly DiscountService $discountService,
    protected readonly PriceService $priceService
)
```

### Основные методы

#### increment()

Увеличивает количество товара в корзине или добавляет новый товар.

```php
public function increment(int $offerId, int $quantity = 1): Result
```

**Параметры:**
- `$offerId` - ID торгового предложения
- `$quantity` - количество для добавления (по умолчанию 1)

**Возвращает:** `Result` с информацией об успехе операции

**Пример:**

```php
$basketService = BasketFactory::createFromFuser();

// Добавить 1 товар
$result = $basketService->increment(123);

// Добавить 3 товара
$result = $basketService->increment(123, 3);

if ($result->isSuccess()) {
    echo "Товар добавлен в корзину";
} else {
    foreach ($result->getErrors() as $error) {
        echo $error->getMessage();
    }
}
```

#### decrement()

Уменьшает количество товара в корзине.

```php
public function decrement(int $offerId, int $quantity = 1): Result
```

**Особенности:**
- Если после уменьшения количество станет <= 0, товар будет удален из корзины

**Пример:**

```php
$result = $basketService->decrement(123, 2);
```

#### remove()

Полностью удаляет товар из корзины.

```php
public function remove(int $offerId): Result
```

**Пример:**

```php
$result = $basketService->remove(123);
if (!$result->isSuccess()) {
    echo "Товар не найден в корзине";
}
```

#### removeAll()

Очищает всю корзину.

```php
public function removeAll(): Result
```

**Пример:**

```php
$result = $basketService->removeAll();
```

#### getItems()

Получает все товары из корзины с расчетом цен и скидок.

```php
public function getItems(): array
```

**Возвращает:**

```php
[
    [
        'ID' => 123,                        // ID товара в корзине
        'PRODUCT_ID' => 456,                // ID товара
        'NAME' => 'Товар 1',
        'CODE' => 'basket_123',             // Код элемента корзины
        'QUANTITY' => 2,
        'PRICE' => 900.00,                  // Цена со скидкой
        'PRICE_FORMATTED' => '900 руб.',
        'FULL_PRICE' => 1800.00,            // Полная стоимость (цена × количество)
        'FULL_PRICE_FORMATTED' => '1 800 руб.',
        'OLD_PRICE' => 1000.00,             // Цена до скидки (если есть)
        'OLD_PRICE_FORMATTED' => '1 000 руб.',
        'FULL_OLD_PRICE' => 2000.00,
        'FULL_OLD_PRICE_FORMATTED' => '2 000 руб.',
        'DISCOUNT_PERCENT' => 10,            // Процент скидки
        ...
    ],
    ...
]
```

**Пример:**

```php
$items = $basketService->getItems();
foreach ($items as $item) {
    echo "{$item['NAME']}: {$item['PRICE_FORMATTED']} × {$item['QUANTITY']} шт.";
    if ($item['DISCOUNT_PERCENT']) {
        echo " (скидка {$item['DISCOUNT_PERCENT']}%)";
    }
}
```

#### getBasketData()

Получает полные данные корзины: товары + итоговую информацию.

```php
public function getBasketData(): array
```

**Возвращает:**

```php
[
    'ITEMS' => [...],              // Массив товаров из getItems()
    'COUPON' => 'SUMMER2025',      // Примененный купон (если есть)
    'SUMMARY' => [
        'TOTAL_QUANTITY' => 5,                    // Общее количество позиций
        'TOTAL_PRICE' => 4500.00,                 // Итоговая сумма
        'TOTAL_PRICE_FORMATTED' => '4 500 руб.',
        'TOTAL_DISCOUNT' => 500.00,               // Общая скидка
        'TOTAL_DISCOUNT_FORMATTED' => '500 руб.'
    ]
]
```

**Пример:**

```php
$data = $basketService->getBasketData();

echo "В корзине товаров: {$data['SUMMARY']['TOTAL_QUANTITY']}";
echo "Итого: {$data['SUMMARY']['TOTAL_PRICE_FORMATTED']}";
if ($data['SUMMARY']['TOTAL_DISCOUNT'] > 0) {
    echo "Скидка: {$data['SUMMARY']['TOTAL_DISCOUNT_FORMATTED']}";
}
if ($data['COUPON']) {
    echo "Применен купон: {$data['COUPON']}";
}
```

#### changeProductQuantityInBasket()

Устанавливает точное количество товара в корзине.

```php
public function changeProductQuantityInBasket(int $offerId, int $quantity): Result
```

**Пример:**

```php
// Установить количество = 5
$result = $basketService->changeProductQuantityInBasket(123, 5);
```

#### applyCoupon()

Применяет купон к корзине.

```php
public function applyCoupon(string $couponCode): Result
```

**Пример:**

```php
$result = $basketService->applyCoupon('SUMMER2025');
if ($result->isSuccess()) {
    echo "Купон применен";
} else {
    echo "Неверный купон";
}
```

#### getIds()

Получает массив ID всех товаров в корзине.

```php
public function getIds(): array
```

**Пример:**

```php
$ids = $basketService->getIds();
// [123, 456, 789]
```

#### getOffersQuantity()

Получает количество позиций (не товаров!) в корзине.

```php
public function getOffersQuantity(): int
```

**Пример:**

```php
$count = $basketService->getOffersQuantity();
// 3 (если в корзине 3 разных товара, даже если общее количество = 10)
```

#### getBasket()

Получает объект корзины Bitrix.

```php
public function getBasket(): BasketBase
```

### Внутренние методы

#### checkQuantity()

Проверяет доступность товара на складе.

```php
protected function checkQuantity(int $offerId, int $quantity): Result
```

#### getExistBasketItems()

Получает элементы корзины для конкретного товара.

```php
public function getExistBasketItems(int $offerId): array
```

---

## BasketFactory

### Описание

Фабрика для создания экземпляров `BasketService` и связанных объектов.

### Конструктор

```php
public function __construct(
    protected readonly string $siteId,
    protected readonly DiscountFactory $discountFactory,
    protected readonly FuserRepository $fuserRepository
)
```

### Методы

#### createBasketForCurrentUser()

Создает корзину для текущего пользователя (по Fuser).

```php
public function createBasketForCurrentUser(): BasketBase
```

#### createBasket()

Создает корзину для конкретного пользователя.

```php
public function createBasket(int $userId): ?BasketBase
```

#### createBasketService()

Создает `BasketService` для переданной корзины.

```php
public function createBasketService(
    BasketBase $basket,
    ?ProductRepositoryContract $productsRepository = null,
    ?OfferRepositoryContract $offersRepository = null
): BasketService
```

#### createBasketServiceForCurrentUser()

Создает `BasketService` для текущего пользователя (наиболее часто используемый метод).

```php
public function createBasketServiceForCurrentUser(
    ?ProductRepositoryContract $productsRepository = null,
    ?OfferRepositoryContract $offersRepository = null
): BasketService
```

**Пример:**

```php
use Beeralex\Catalog\Service\Basket\BasketFactory;

$basketFactory = service(BasketFactory::class);

// Для текущего пользователя
$basketService = $basketFactory->createBasketServiceForCurrentUser();

// Для конкретного пользователя
$basket = $basketFactory->createBasket(123);
if ($basket) {
    $basketService = $basketFactory->createBasketService($basket);
}
```

---

## DiscountService

### Описание

Сервис для работы со скидками. Вычисляет финальные цены товаров с учетом всех скидок.

### Конструктор

```php
public function __construct(
    protected readonly BasketBase $basket
)
```

### Методы

#### getPrice()

Получает цену товара из корзины с учетом скидок.

```php
public function getPrice(int|string $basketCode): ?float
```

**Параметры:**
- `$basketCode` - код элемента корзины (ID или CODE)

**Возвращает:** финальную цену или `null`

**Пример:**

```php
$discountService = new DiscountService($basket);
$price = $discountService->getPrice('basket_123');
```

#### getDiscounts()

Получает все примененные скидки.

```php
public function getDiscounts(): array
```

**Возвращает:** массив со всеми скидками, примененными к корзине

---

## CouponsService

### Описание

Сервис для работы с купонами.

### Методы

#### getApplyedCoupon()

Получает примененный купон.

```php
public function getApplyedCoupon(): string
```

**Возвращает:** код купона или пустую строку

**Пример:**

```php
$couponsService = service(CouponsService::class);
$coupon = $couponsService->getApplyedCoupon();
if ($coupon) {
    echo "Применен купон: {$coupon}";
}
```

#### clearCoupons()

Очищает все примененные купоны.

```php
public function clearCoupons(): void
```

#### applyCoupon()

Применяет купон.

```php
public function applyCoupon(string $couponCode): Result
```

**Пример:**

```php
$result = $couponsService->applyCoupon('SUMMER2025');
if (!$result->isSuccess()) {
    echo "Купон недействителен";
}
```

---

## BasketUtils

### Описание

Вспомогательный класс для работы с корзиной. Используется внутри `BasketService`.

### Методы

- `getItems()` - получает элементы корзины с дополнительной информацией о товарах
- `getOffersIds()` - получает ID всех товаров в корзине

---

## Практические примеры

### Полный цикл работы с корзиной

```php
use Beeralex\Catalog\Service\Basket\BasketFactory;

// Создаем сервис корзины
$basketFactory = service(BasketFactory::class);
$basketService = $basketFactory->createBasketServiceForCurrentUser();

// Добавляем товары
$basketService->increment(123, 2);  // 2 шт товара #123
$basketService->increment(456, 1);  // 1 шт товара #456

// Применяем купон
$basketService->applyCoupon('WINTER2025');

// Получаем данные корзины
$data = $basketService->getBasketData();

// Выводим информацию
foreach ($data['ITEMS'] as $item) {
    echo "{$item['NAME']}: {$item['PRICE_FORMATTED']} × {$item['QUANTITY']} = {$item['FULL_PRICE_FORMATTED']}\n";
    if ($item['OLD_PRICE']) {
        echo "Скидка: {$item['DISCOUNT_PERCENT']}%\n";
    }
}

echo "Итого: {$data['SUMMARY']['TOTAL_PRICE_FORMATTED']}\n";
echo "Экономия: {$data['SUMMARY']['TOTAL_DISCOUNT_FORMATTED']}\n";
```

### AJAX добавление в корзину

```php
// ajax_add_to_basket.php
use Beeralex\Catalog\Service\Basket\BasketFactory;

$offerId = (int)$_POST['offer_id'];
$quantity = (int)$_POST['quantity'] ?: 1;

$basketFactory = service(BasketFactory::class);
$basketService = $basketFactory->createBasketServiceForCurrentUser();

$result = $basketService->increment($offerId, $quantity);

if ($result->isSuccess()) {
    $data = $basketService->getBasketData();
    echo json_encode([
        'success' => true,
        'basket' => [
            'count' => $data['SUMMARY']['TOTAL_QUANTITY'],
            'total' => $data['SUMMARY']['TOTAL_PRICE_FORMATTED']
        ]
    ]);
} else {
    echo json_encode([
        'success' => false,
        'errors' => array_map(fn($e) => $e->getMessage(), $result->getErrors())
    ]);
}
```

### Обновление количества

```php
// ajax_update_quantity.php
$offerId = (int)$_POST['offer_id'];
$quantity = (int)$_POST['quantity'];

$basketService = $basketFactory->createBasketServiceForCurrentUser();

if ($quantity > 0) {
    $result = $basketService->changeProductQuantityInBasket($offerId, $quantity);
} else {
    $result = $basketService->remove($offerId);
}

if ($result->isSuccess()) {
    echo json_encode([
        'success' => true,
        'basket' => $basketService->getBasketData()
    ]);
}
```

### Применение купона через форму

```php
// ajax_apply_coupon.php
$couponCode = trim($_POST['coupon']);

$basketService = $basketFactory->createBasketServiceForCurrentUser();
$result = $basketService->applyCoupon($couponCode);

if ($result->isSuccess()) {
    $data = $basketService->getBasketData();
    echo json_encode([
        'success' => true,
        'coupon' => $data['COUPON'],
        'discount' => $data['SUMMARY']['TOTAL_DISCOUNT_FORMATTED']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Купон недействителен'
    ]);
}
```

---

## Валидация и обработка ошибок

Все методы корзины возвращают `Result`, который нужно проверять:

```php
$result = $basketService->increment(123, 5);

if ($result->isSuccess()) {
    // Успешно
} else {
    // Обработка ошибок
    foreach ($result->getErrors() as $error) {
        switch ($error->getCode()) {
            case 'basket':
                echo "Ошибка корзины: " . $error->getMessage();
                break;
            default:
                echo $error->getMessage();
        }
    }
}
```

### Типичные ошибки

- **"Количество должно быть больше 0"** - попытка добавить 0 или отрицательное количество
- **"Товара нет в наличии"** - недостаточное количество на складе
- **"Товар не найден в корзине"** - попытка удалить несуществующий товар
- **"Ошибка при применении купона"** - купон неактивен или не существует

---

## Интеграция с компонентами

```php
class BasketComponent extends CBitrixComponent
{
    protected BasketService $basketService;
    
    public function executeComponent()
    {
        $basketFactory = service(BasketFactory::class);
        $this->basketService = $basketFactory->createBasketServiceForCurrentUser();
        
        $this->arResult = $this->basketService->getBasketData();
        $this->includeComponentTemplate();
    }
}
```

---

## Рекомендации

1. **Всегда проверяйте Result** - не забывайте проверять `isSuccess()`
2. **Используйте фабрику** - не создавайте сервисы вручную
3. **Кешируйте BasketService** - создавайте один раз и переиспользуйте в рамках запроса
4. **Обрабатывайте ошибки** - показывайте пользователю понятные сообщения
5. **Применяйте купоны осторожно** - перед применением проверяйте валидность
6. **Используйте транзакции** - для критичных операций оборачивайте в try-catch
