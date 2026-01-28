# Модуль beeralex.catalog

## Описание

Модуль `beeralex.catalog` - это расширенная система управления каталогом товаров для Bitrix. Модуль предоставляет унифицированные интерфейсы для работы с товарами, предложениями (SKU), корзиной, заказами и интеграцией с системой продаж.

## Основные возможности

- 🛍️ **Управление каталогом**: работа с товарами и предложениями через унифицированные репозитории
- 💰 **Система цен и скидок**: расчет цен, применение скидок и купонов
- 🛒 **Корзина**: расширенное управление корзиной покупателя
- 📦 **Заказы**: обработка заказов и свойств заказа
- 🌍 **Геолокация**: автоматическое определение местоположения через DaData API
- 💳 **Кассы и оплата**: кастомные чеки и ограничения доступа
- 🔍 **Поиск**: быстрый поиск товаров по каталогу

## Архитектура

Модуль построен на принципах чистой архитектуры с использованием:

- **Repository Pattern** - для работы с данными
- **Service Layer** - для бизнес-логики
- **Dependency Injection** - через `.settings.php` модуля (с возможностью переопределения в `.settings_extra.php`)
- **Contracts (Interfaces)** - для гибкости и тестируемости

## Структура модуля

```
beeralex.catalog/
├── .settings.php             # Регистрация сервисов в DI контейнере
├── install/
│   └── index.php              # Установщик модуля
├── lib/
│   ├── EventHandlers.php      # Обработчики событий Bitrix
│   ├── Options.php            # Настройки модуля
│   ├── Cashbox/              # Кастомные чеки для касс
│   ├── Contracts/            # Интерфейсы репозиториев
│   ├── Dto/                  # Data Transfer Objects
│   ├── Enum/                 # Перечисления (статусы, ключи DI)
│   ├── ExtraService/         # Дополнительные сервисы доставки
│   ├── Location/             # Геолокация и интеграция с API
│   ├── Repository/           # Репозитории данных
│   ├── Restriction/          # Ограничения для платежей/доставки
│   └── Service/              # Бизнес-логика
└── docs/                     # Документация
```

## Установка

### Требования

- PHP 8.2+
- Bitrix Framework 25.0+ (рекомендуемая для php 8.2)
- Модули: `beeralex.core`

### Процесс установки

1. Разместите модуль в директории `/local/modules/beeralex.catalog/`
2. Установите модуль через административную панель Bitrix или через CLI
3. Модуль автоматически зарегистрирует все необходимые сервисы в DI контейнере через свой `.settings.php`

### Переопределение сервисов (опционально)

Если вам нужно переопределить стандартные репозитории или сервисы своими реализациями, создайте файл `/local/.settings_extra.php` в корне проекта:

```php
use Beeralex\Catalog\Enum\DIServiceKey;
use App\Repository\ProductsRepository; // Ваша реализация

return [
    'services' => [
        'value' => [
            // Переопределяем стандартный репозиторий товаров
            DIServiceKey::PRODUCT_REPOSITORY->value => [
                'constructor' => static function () {
                    return new ProductsRepository(
                        'catalog',
                        service(\Beeralex\Core\Service\CatalogService::class),
                        service(\Beeralex\Catalog\Repository\CatalogViewedProductRepository::class),
                        service(\Beeralex\Core\Service\UrlService::class)
                    );
                }
            ],
        ]
    ]
];
```

**Важно:** Сервисы, зарегистрированные в `.settings_extra.php`, переопределяют сервисы из модулей. Это позволяет расширять функциональность без изменения кода модулей.

## Основные компоненты

### 1. Репозитории

- [ProductsRepository](./repositories.md#productsrepository) - работа с товарами
- [OffersRepository](./repositories.md#offersrepository) - работа с предложениями (SKU)
- [OrderRepository](./repositories.md#orderrepository) - работа с заказами
- [PriceRepository](./repositories.md#pricerepository) - работа с ценами
- [StoreRepository](./repositories.md#storerepository) - работа со складами

### 2. Сервисы

- [CatalogService](./services.md#catalogservice) - основной сервис каталога
- [BasketService](./basket-services.md#basketservice) - управление корзиной
- [OrderService](./services.md#orderservice) - обработка заказов
- [PriceService](./services.md#priceservice) - расчет цен
- [DiscountService](./basket-services.md#discountservice) - применение скидок
- [SearchService](./services.md#searchservice) - поиск товаров

### 3. Система Location

- [BitrixLocationResolver](./location.md#bitrixlocationresolver) - определение локации в Bitrix
- [DadataService](./location.md#dadataservice) - интеграция с DaData API
- [LocationApiClientContract](./location.md#contracts) - контракт для API клиентов

### 4. Расширения Sale

- [PrepaymentCheck](./sale-extensions.md#prepaymentcheck) - чек частичной предоплаты
- [UserRestriction](./sale-extensions.md#userrestriction) - ограничение по пользователям
- [MyPriceExtraService](./sale-extensions.md#mypriceextraservice) - кастомная цена доставки

## Быстрый старт

### Получение товаров с предложениями

```php
use Beeralex\Catalog\Contracts\ProductRepositoryContract;

$productRepo = service(ProductRepositoryContract::class);
$products = $productRepo->getProducts([1, 2, 3]);

// С предложениями и ценами
$catalogService = service(\Beeralex\Catalog\Service\CatalogService::class);
$productsWithOffers = $catalogService->getProductsWithOffers([1, 2, 3], true, true);
```

### Работа с корзиной

```php
use Beeralex\Catalog\Service\Basket\BasketFactory;

$basketService = BasketFactory::createFromFuser();

// Добавить товар
$basketService->increment($offerId = 123, $quantity = 2);

// Удалить товар
$basketService->remove($offerId = 123);

// Применить купон
$basketService->applyCoupon('SUMMER2025');
```

### Определение местоположения

```php
use Beeralex\Catalog\Location\Contracts\BitrixLocationResolverContract;

$resolver = service(BitrixLocationResolverContract::class);
$location = $resolver->getBitrixLocationByAddress('Москва, Красная площадь, 1');

// Результат: ['city' => 'Москва', 'code' => '0000073738', ...]
```

## Конфигурация

### Опции модуля

```php
use Beeralex\Catalog\Options;

$options = service(Options::class);
$apiKey = $options->apiKey;      // API ключ (например, для DaData)
$secretKey = $options->secretKey; // Секретный ключ
```

### События (Events)

Модуль регистрирует следующие обработчики событий:

| Событие | Обработчик | Описание |
|---------|-----------|----------|
| `onSalePaySystemRestrictionsClassNamesBuildList` | `EventHandlers::onSalePaySystemRestrictionsClassNamesBuildList` | Регистрация ограничений платежных систем |
| `onSaleCashboxRestrictionsClassNamesBuildList` | `EventHandlers::onSaleCashboxRestrictionsClassNamesBuildList` | Регистрация ограничений касс |
| `OnGetCustomCheckList` | `EventHandlers::onGetCustomCheckList` | Регистрация кастомных чеков |
| `onSaleDeliveryExtraServicesClassNamesBuildList` | `EventHandlers::onSaleDeliveryExtraServicesClassNamesBuildList` | Регистрация дополнительных сервисов доставки |

## Расширение функционала

### Создание собственного репозитория

Модуль предоставляет базовые реализации всех репозиториев и сервисов. Для расширения функциональности создайте свой класс-наследник:

**Шаг 1:** Создайте свой репозиторий в `/local/lib/App/Repository/`

```php
namespace App\Repository;

use Beeralex\Catalog\Repository\ProductsRepository as BaseRepository;

class ProductsRepository extends BaseRepository
{
    public function getProducts(array $productIds, bool $onlyActive = true): array
    {
        // Расширенная логика получения товаров
        $products = parent::getProducts($productIds, $onlyActive);
        
        // Добавляем дополнительные поля
        $fileService = service(FileService::class);
        // ... ваша логика обработки картинок, свойств и т.д.
        
        return $products;
    }
}
```

**Шаг 2:** Зарегистрируйте его в `/local/.settings_extra.php`

```php
use Beeralex\Catalog\Enum\DIServiceKey;
use App\Repository\ProductsRepository;

return [
    'services' => [
        'value' => [
            DIServiceKey::PRODUCT_REPOSITORY->value => [
                'constructor' => static function () {
                    return new ProductsRepository(
                        'catalog',
                        service(\Beeralex\Core\Service\CatalogService::class),
                        service(\Beeralex\Catalog\Repository\CatalogViewedProductRepository::class),
                        service(\Beeralex\Core\Service\UrlService::class)
                    );
                }
            ],
        ]
    ]
];
```

**Шаг 3:** Теперь все обращения к `ProductRepositoryContract` будут использовать вашу реализацию

```php
// В любом месте проекта
$productRepo = service(\Beeralex\Catalog\Contracts\ProductRepositoryContract::class);
// Вернет вашу реализацию App\Repository\ProductsRepository
```

## Enum классы

### DIServiceKey

Ключи для регистрации сервисов в DI контейнере:

```php
enum DIServiceKey: string
{
    case PRODUCT_REPOSITORY = 'beeralex.catalog.product.repository';
    case OFFERS_REPOSITORY = 'beeralex.catalog.offer.repository';
    case EMPTY_OFFERS_REPOSITORY = 'beeralex.catalog.empty.offer.repository';
    case SORTING_REPOSITORY = 'beeralex.catalog.sorting.repository';
    case SORTING_SERVICE = 'beeralex.catalog.sorting.service';
}
```

### SaleStatuses

Статусы заказов:

```php
enum SaleStatuses : string
{
    case DA = 'DA';  // Комплектация заказа
    case DF = 'DF';  // Отгружен
    case DG = 'DG';  // Ожидаем приход товара
    case DN = 'DN';  // Ожидает обработки
    case DS = 'DS';  // Передан в службу доставки
    case DT = 'DT';  // Ожидаем забора транспортной компанией
    case F = 'F';    // Выполнен
    case N = 'N';    // Принят, ожидается оплата
    case P = 'P';    // Оплачен, формируется к отправке
}
```

## Дополнительная документация

- [Репозитории](./repositories.md) - подробное описание всех репозиториев
- [Сервисы](./services.md) - описание сервисного слоя
- [Корзина и скидки](./basket-services.md) - работа с корзиной и применение скидок
- [Система Location](./location.md) - геолокация и определение адресов
- [Расширения Sale](./sale-extensions.md) - кастомизация модуля продаж
- [Примеры использования](./examples.md) - практические примеры

## Лицензия

Проприетарный модуль. © beeralex

## Поддержка

При возникновении вопросов обращайтесь к разработчикам модуля.
