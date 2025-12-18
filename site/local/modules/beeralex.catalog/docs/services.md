# Сервисы

Сервисный слой модуля `beeralex.catalog` содержит бизнес-логику для работы с каталогом, заказами, ценами и поиском. Сервисы используют репозитории для доступа к данным и предоставляют высокоуровневые методы для работы с каталогом.

---

## CatalogService

### Описание

Главный сервис модуля, который объединяет работу с товарами, предложениями, ценами и скидками. Наследуется от `CoreCatalogService` и расширяет его функционал.

### Конструктор

```php
public function __construct(
    protected readonly ProductRepositoryContract $productsRepository,
    protected readonly OfferRepositoryContract $offersRepository,
    protected readonly CatalogViewedProductRepository $viewedProductRepository,
    protected readonly PriceTypeRepository $priceTypeRepository,
    protected readonly SortingService $sortingService,
    protected readonly DiscountFactory $discountFactory,
    protected readonly CatalogSectionsService $catalogSectionsService,
    protected readonly SearchService $searchService
)
```

### Основные методы

#### getProductsWithOffers()

Возвращает товары с их предложениями и опционально применяет скидки.

```php
public function getProductsWithOffers(
    array $productIds,
    bool $isAvailable = true,
    bool $applyDiscounts = false
): array
```

**Параметры:**
- `$productIds` - массив ID товаров
- `$isAvailable` - только доступные товары (в наличии)
- `$applyDiscounts` - применить скидки к ценам

**Возвращает:**

```php
[
    [
        'ID' => 100,
        'NAME' => 'Товар 1',
        'DETAIL_PAGE_URL' => '/catalog/tovar-1/',
        'PRICE' => [...],
        'OFFERS' => [
            [
                'ID' => 201,
                'NAME' => 'Размер S',
                'DETAIL_PAGE_URL' => '/catalog/tovar-1/size-s/',
                'PRICE' => [...],
                'CATALOG' => ['AVAILABLE' => 'Y', 'QUANTITY' => 10],
            ],
            [
                'ID' => 202,
                'NAME' => 'Размер M',
                ...
            ],
        ],
        'PRESELECTED_OFFER' => [...], // Первое предложение по умолчанию
    ],
]
```

**Особенности:**
- Автоматически формирует URL для предложений на основе шаблона `#PRODUCT_URL#`
- Выбирает первое предложение как предвыбранное (`PRESELECTED_OFFER`)
- При `$applyDiscounts = true` рассчитывает скидки и добавляет discount price

**Пример:**

```php
use Beeralex\Catalog\Service\CatalogService;

$catalogService = service(CatalogService::class);

// Получить товары с предложениями без скидок
$products = $catalogService->getProductsWithOffers([1, 2, 3], true, false);

// Получить товары с предложениями и скидками
$productsWithDiscounts = $catalogService->getProductsWithOffers([1, 2, 3], true, true);
```

#### makeUrl()

Формирует URL с учетом параметров сортировки и поиска.

```php
public function makeUrl(string $url): string
```

**Пример:**

```php
$url = $catalogService->makeUrl('/catalog/section/');
// Результат: /catalog/section/?sort=price&q=iPhone
```

#### search()

Выполняет поиск товаров по запросу и возвращает найденные товары и секции.

```php
public function search(
    string $query,
    int $searchLimit = 50,
    int $realLimit = 7
): array
```

**Параметры:**
- `$query` - поисковый запрос
- `$searchLimit` - сколько товаров искать (для внутреннего поиска)
- `$realLimit` - сколько товаров вернуть в результате

**Возвращает:**

```php
[
    'PRODUCTS' => [...], // Массив товаров с предложениями
    'SECTIONS' => [...], // Массив секций, в которых найдены товары
]
```

**Пример:**

```php
$result = $catalogService->search('iPhone', 50, 10);
$products = $result['PRODUCTS'];
$sections = $result['SECTIONS'];
```

#### updateProductPrices()

Внутренний метод для обновления цен товара после применения скидки.

```php
protected function updateProductPrices(array &$product, float $discountedPrice): void
```

Добавляет новую цену со скидкой в массив цен товара.

#### applyDiscounts()

Внутренний метод для применения скидок ко всем товарам и предложениям.

```php
protected function applyDiscounts(array &$products): void
```

---

## OrderService

### Описание

Сервис для работы с заказами. Предоставляет методы для получения свойств заказа, работы с корзиной заказа и расчета различных показателей.

### Конструктор

```php
public function __construct(
    protected readonly PersonTypeRepository $personTypeRepository
)
```

### Основные методы

#### getPropertyValues()

Получает значения свойств заказа в виде ассоциативного массива.

```php
public function getPropertyValues(PropertyValueCollectionBase $collection): array
```

**Возвращает:**

```php
[
    'NAME' => 'Иван',
    'EMAIL' => 'ivan@example.com',
    'PHONE' => '+79991234567',
    'ADDRESS' => 'г. Москва, ул. Ленина, д. 1',
    ...
]
```

**Пример:**

```php
$order = \Bitrix\Sale\Order::load(123);
$props = $orderService->getPropertyValues($order->getPropertyCollection());
$email = $props['EMAIL'];
```

#### getProperties()

Получает объекты свойств заказа в виде ассоциативного массива.

```php
public function getProperties(PropertyValueCollectionBase $collection): array
```

**Возвращает:**

```php
[
    'NAME' => PropertyValue,    // Объект свойства
    'EMAIL' => PropertyValue,
    ...
]
```

#### getPropertyList()

Получает список всех доступных свойств для типа плательщика.

```php
public function getPropertyList(?int $persontTypeId = null): array
```

**Пример:**

```php
$props = $orderService->getPropertyList(1);
/*
[
    'NAME' => [
        'ID' => 1,
        'CODE' => 'NAME',
        'NAME' => 'Имя',
        'TYPE' => 'TEXT',
        'REQUIRED' => 'Y',
        ...
    ],
    'EMAIL' => [...],
    ...
]
*/
```

#### getBasketItemWithMaxPrice()

Получает элемент корзины с максимальной ценой.

```php
public function getBasketItemWithMaxPrice(Order $order): ?BasketItem
```

**Пример:**

```php
$maxItem = $orderService->getBasketItemWithMaxPrice($order);
if ($maxItem) {
    echo "Самый дорогой товар: " . $maxItem->getField('NAME');
}
```

#### getQuantity()

Получает общее количество товаров в заказе.

```php
public function getQuantity(Order $order): int
```

**Пример:**

```php
$totalQuantity = $orderService->getQuantity($order);
// Например: 5 (если в корзине 2 товара по 2 шт. и 1 товар 1 шт.)
```

---

## PriceService

### Описание

Сервис для работы с ценами: форматирование, расчет процента скидки.

### Основные методы

#### format()

Форматирует цену для отображения на сайте.

```php
public function format(?float $price): string
```

**Пример:**

```php
$priceService = service(PriceService::class);
echo $priceService->format(1234.50);
// Результат: "1 234,50 руб."
```

#### getSalePercent()

Рассчитывает процент скидки.

```php
public function getSalePercent(?float $oldPrice, ?float $newPrice): int
```

**Пример:**

```php
$discount = $priceService->getSalePercent(1000, 750);
// Результат: 25 (скидка 25%)
```

#### getBaseCurrency()

Получает базовую валюту сайта.

```php
public function getBaseCurrency(): string
```

**Пример:**

```php
$currency = $priceService->getBaseCurrency();
// Результат: "RUB"
```

---

## SearchService

### Описание

Сервис для поиска товаров по каталогу с использованием встроенного поиска Bitrix.

### Константы

```php
public const REQUEST_PARAM = 'q'; // Параметр запроса для поиска
```

### Конструктор

```php
public function __construct(
    protected readonly \CAllSearch $search,
    protected readonly ProductRepositoryContract $productRepository,
    protected readonly LanguageService $languageService
)
```

### Основные методы

#### getProductsIds()

Ищет товары по запросу и возвращает их ID.

```php
public function getProductsIds(string $query, int $limit): array
```

**Особенности:**
- Использует модуль поиска Bitrix
- Автоматически пытается транслитерировать запрос, если ничего не найдено
- Исключает секции из результатов (только товары)
- Сортирует по релевантности (RANK)

**Пример:**

```php
$searchService = service(SearchService::class);

// Поиск по кириллице
$ids = $searchService->getProductsIds('iPhone', 20);
// [1, 5, 12, 45, ...]

// Если пользователь ввел латиницу в русской раскладке
$ids = $searchService->getProductsIds('ашзщту', 20);
// Автоматически транслитерируется в "iPhone" и находит товары
```

#### issetTranslitirate()

Внутренний метод для проверки, содержит ли строка латиницу.

```php
protected function issetTranslitirate(string $str): bool
```

---

## CatalogSectionsService

### Описание

Сервис для работы с разделами каталога.

### Основные методы

#### getSections()

Получает разделы, в которых находятся указанные товары.

```php
public function getSections(array $productIds): array
```

---

## CatalogElementService

### Описание

Сервис для работы с элементами каталога (детальные страницы товаров).

---

## Использование сервисов

### Через DI контейнер

```php
use Beeralex\Catalog\Service\CatalogService;
use Beeralex\Catalog\Service\OrderService;
use Beeralex\Catalog\Service\PriceService;

$catalogService = service(CatalogService::class);
$orderService = service(OrderService::class);
$priceService = service(PriceService::class);
```

### В компонентах

```php
class MyComponent extends CBitrixComponent
{
    protected CatalogService $catalogService;
    
    public function onPrepareComponentParams($params)
    {
        $this->catalogService = service(CatalogService::class);
        return parent::onPrepareComponentParams($params);
    }
    
    public function executeComponent()
    {
        $products = $this->catalogService->getProductsWithOffers([1, 2, 3], true, true);
        $this->arResult['PRODUCTS'] = $products;
        $this->includeComponentTemplate();
    }
}
```

### Комплексный пример

```php
use Beeralex\Catalog\Service\CatalogService;
use Beeralex\Catalog\Service\SearchService;
use Beeralex\Catalog\Service\PriceService;

// Поиск товаров
$searchService = service(SearchService::class);
$searchQuery = $_GET['q'] ?? '';

if ($searchQuery) {
    $productIds = $searchService->getProductsIds($searchQuery, 50);
    
    // Получение товаров с предложениями и скидками
    $catalogService = service(CatalogService::class);
    $products = $catalogService->getProductsWithOffers(
        array_slice($productIds, 0, 20),
        true,
        true
    );
    
    // Форматирование цен
    $priceService = service(PriceService::class);
    foreach ($products as &$product) {
        if (isset($product['PRICE'][0]['PRICE'])) {
            $product['PRICE_FORMATTED'] = $priceService->format(
                $product['PRICE'][0]['PRICE']
            );
        }
    }
}
```

---

## Расширение сервисов

Вы можете создать свои сервисы, наследуясь от базовых:

```php
namespace App\Service;

use Beeralex\Catalog\Service\CatalogService as BaseCatalogService;

class CatalogService extends BaseCatalogService
{
    public function getProductsWithReviews(array $productIds): array
    {
        $products = $this->getProductsWithOffers($productIds, true, true);
        
        // Добавляем отзывы
        foreach ($products as &$product) {
            $product['REVIEWS'] = $this->getReviewsForProduct($product['ID']);
        }
        
        return $products;
    }
    
    private function getReviewsForProduct(int $productId): array
    {
        // Ваша логика получения отзывов
        return [];
    }
}
```

Затем зарегистрируйте в `/local/.settings_extra.php`:

```php
use Beeralex\Catalog\Service\CatalogService;
use App\Service\CatalogService as AppCatalogService;

return [
    'services' => [
        'value' => [
            // Переопределяем стандартный CatalogService модуля
            CatalogService::class => [
                'constructor' => static function() {
                    return new AppCatalogService(
                        service(\Beeralex\Catalog\Contracts\ProductRepositoryContract::class),
                        service(\Beeralex\Catalog\Contracts\OfferRepositoryContract::class),
                        // ... остальные зависимости
                    );
                }
            ]
        ]
    ]
];
```

---

## Рекомендации

1. **Используйте сервисы через DI** - это обеспечивает гибкость и тестируемость
2. **Не создавайте сервисы вручную** - используйте `service()` helper
3. **Кешируйте результаты** дорогих операций на уровне сервисов
4. **Разделяйте логику** - создавайте новые сервисы для новой функциональности
5. **Применяйте скидки осознанно** - метод `applyDiscounts` может быть ресурсоемким для больших каталогов
