# Репозитории

Репозитории в модуле `beeralex.catalog` обеспечивают унифицированный доступ к данным каталога. Все репозитории работают через ORM Bitrix и предоставляют удобные методы для выборки и фильтрации данных.

## Базовый класс AbstractCatalogRepository

### Описание

Абстрактный репозиторий, от которого наследуются все репозитории каталога. Предоставляет базовые методы для работы с элементами инфоблоков с автоматическим подключением цен, складов и характеристик товара.

### Конструктор

```php
public function __construct(
    string $iblockCode,                           // Символьный код инфоблока
    protected readonly CatalogService $catalogService,  // Сервис каталога
    protected readonly UrlService $urlService     // Сервис генерации URL
)
```

### Основные методы

#### findAll()

Универсальный метод получения элементов каталога с ценами, складами и параметрами товара.

```php
public function findAll(
    array $filter = [],    // Фильтр ORM
    array $select = ['*'], // Поля для выборки
    array $order = ['SORT' => 'ASC'], // Сортировка
    ?int $limit = null,    // Лимит
    ?int $offset = null    // Смещение
): array
```

**Параметры `$select`:**

Можно использовать специальные алиасы для автоматического подключения связанных данных:

- `'*'` - все поля элемента + runtime-поля (автоматически включает CATALOG, PRICE, STORE_PRODUCT)
- `'CATALOG'` - все поля товара (количество, вес, артикул и т.д.)
- `'PRICE'` - подгрузить цены товара
- `'PRICE.CATALOG_GROUP'` - подгрузить группы цен
- `'STORE_PRODUCT'` - подгрузить остатки по складам

**Пример использования:**

```php
$items = $repository->findAll(
    ['ACTIVE' => 'Y', 'IBLOCK_SECTION_ID' => 10],
    ['*', 'PRICE', 'STORE_PRODUCT'],
    ['SORT' => 'ASC', 'NAME' => 'ASC'],
    20,
    0
);
```

**Результат:**

```php
[
    [
        'ID' => 123,
        'NAME' => 'Товар 1',
        'CODE' => 'tovar-1',
        'DETAIL_PAGE_URL' => '/catalog/tovar-1/',
        'PRICE' => [
            [
                'ID' => 456,
                'PRICE' => 1000.00,
                'CURRENCY' => 'RUB',
                'CATALOG_GROUP' => [...],
            ]
        ],
        'STORE_PRODUCT' => [
            ['ID' => 1, 'AMOUNT' => 10, 'STORE_ID' => 1],
            ['ID' => 2, 'AMOUNT' => 5, 'STORE_ID' => 2],
        ],
        'CATALOG' => [
            'QUANTITY' => 15,
            'WEIGHT' => 500,
            'AVAILABLE' => 'Y',
        ],
    ],
    ...
]
```

#### buildQuery()

Внутренний метод для построения ORM запроса с автоматическим подключением необходимых связей.

```php
protected function buildQuery(
    array $filter = [],
    array $select = ['*'],
    array $order = ['SORT' => 'ASC'],
    ?int $limit = null,
    ?int $offset = null
): Query
```

---

## ProductsRepository

### Описание

Репозиторий для работы с товарами каталога. Расширяет `AbstractCatalogRepository` и добавляет специфичные методы для работы с товарами.

### Контракт

Реализует интерфейс `ProductRepositoryContract`.

### Конструктор

```php
public function __construct(
    string $iblockCode,
    CatalogService $catalogService,
    protected readonly CatalogViewedProductRepository $catalogViewedProductRepository,
    UrlService $urlService
)
```

### Основные методы

#### getProducts()

Получает товары по ID со связкой цен и всеми необходимыми данными.

```php
public function getProducts(array $productIds, bool $onlyActive = true): array
```

**Параметры:**
- `$productIds` - массив ID товаров
- `$onlyActive` - фильтровать только активные товары

**Пример:**

```php
$products = $productRepo->getProducts([1, 2, 3], true);
// Вернет товары в том же порядке, что и в массиве $productIds
```

#### getAvailableProductIds()

Возвращает список ID активных и доступных товаров.

```php
public function getAvailableProductIds(array $filter = []): array
```

**Пример:**

```php
$ids = $productRepo->getAvailableProductIds(['IBLOCK_SECTION_ID' => 10]);
// [1, 2, 3, 4, ...]
```

#### getSameProductsIds()

Получает ID похожих товаров из той же секции (для блока "Похожие товары").

```php
public function getSameProductsIds(
    int $elementId,
    int $sectionId,
    int $limit = 15,
    int $cacheTtl = 0
): array
```

**Параметры:**
- `$elementId` - ID текущего товара (будет исключен из результата)
- `$sectionId` - ID секции
- `$limit` - максимальное количество товаров
- `$cacheTtl` - время кеширования в секундах (0 = без кеша)

**Пример:**

```php
$similarIds = $productRepo->getSameProductsIds(
    elementId: 100,
    sectionId: 5,
    limit: 10,
    cacheTtl: 3600
);
```

#### getNewProductsIds()

Получает ID новых товаров (добавленных за последний период).

```php
public function getNewProductsIds(
    int $limit = 15,
    int $cacheTtl = 0,
    int $countMonts = 1
): array
```

**Параметры:**
- `$limit` - максимальное количество
- `$cacheTtl` - время кеширования
- `$countMonts` - количество месяцев назад

**Пример:**

```php
// Новинки за последние 2 месяца
$newIds = $productRepo->getNewProductsIds(20, 3600, 2);
```

#### getPopularProductsIds()

Получает ID популярных товаров на основе просмотров.

```php
public function getPopularProductsIds(int $limit = 15, int $cacheTtl = 0): array
```

**Пример:**

```php
$popularIds = $productRepo->getPopularProductsIds(15, 3600);
```

#### getViewedProductsIds()

Получает ID товаров, которые просматривал текущий пользователь.

```php
public function getViewedProductsIds(int $currentElementId): array
```

**Параметры:**
- `$currentElementId` - ID текущего товара (будет исключен)

**Пример:**

```php
$viewedIds = $productRepo->getViewedProductsIds(100);
```

#### getProductWithOffers()

Получает товар со всеми его предложениями (SKU).

```php
public function getProductWithOffers(int $productId): ?array
```

**Пример:**

```php
$product = $productRepo->getProductWithOffers(123);
/*
[
    'ID' => 123,
    'NAME' => 'Товар',
    'OFFERS' => [
        ['ID' => 456, 'NAME' => 'Размер S', ...],
        ['ID' => 457, 'NAME' => 'Размер M', ...],
    ]
]
*/
```

---

## OffersRepository

### Описание

Репозиторий для работы с торговыми предложениями (SKU, офферами). Предложения связаны с товарами через свойство `CML2_LINK`.

### Контракт

Реализует интерфейс `OfferRepositoryContract`.

### Конструктор

```php
public function __construct(
    string $iblockCode,
    CatalogService $catalogService,
    UrlService $urlService
)
```

### Основные методы

#### getOfferIdsByProductIds()

Получает карту соответствия ID товаров и их предложений.

```php
public function getOfferIdsByProductIds(
    array $productIds,
    bool $onlyAvailable = true
): array
```

**Результат:**

```php
[
    100 => [201, 202, 203], // У товара 100 три предложения
    101 => [204, 205],      // У товара 101 два предложения
]
```

#### getOffersByIds()

Получает полные данные предложений по их ID.

```php
public function getOffersByIds(array $offerIds): array
```

**Пример:**

```php
$offers = $offerRepo->getOffersByIds([201, 202, 203]);
/*
[
    201 => [
        'ID' => 201,
        'NAME' => 'Размер S',
        'PRICE' => [...],
        'CATALOG' => [...],
        'STORE_PRODUCT' => [...],
    ],
    ...
]
*/
```

#### getOffersByProductIds()

Получает предложения, сгруппированные по товарам.

```php
public function getOffersByProductIds(
    array $productIds,
    bool $onlyAvailable = true
): array
```

**Результат:**

```php
[
    100 => [ // ID товара
        ['ID' => 201, ...], // Первое предложение
        ['ID' => 202, ...], // Второе предложение
    ],
    101 => [
        ['ID' => 204, ...],
    ],
]
```

#### getProductsIdsByOffersIds()

Обратная операция - получает ID товаров по ID предложений.

```php
public function getProductsIdsByOffersIds(array $offersIds): array
```

**Результат:**

```php
[
    201 => 100, // Предложение 201 принадлежит товару 100
    202 => 100,
    203 => 100,
    204 => 101,
]
```

---

## Другие репозитории

### CatalogViewedProductRepository

Хранит информацию о просмотренных пользователем товарах.

### OrderRepository

Работа с заказами.

### PersonTypeRepository

Работа с типами плательщиков.

### PriceRepository

Работа с ценами товаров.

### PriceTypeRepository

Работа с типами цен.

### StoreRepository

Работа со складами и остатками.

### FuserRepository

Работа с виртуальными пользователями корзины.

### SortingRepository

Работа с вариантами сортировки товаров.

---

## Расширение репозиториев

### Пример создания кастомного репозитория

Вы можете создать свой репозиторий, наследуясь от базовых классов:

```php
namespace App\Repository;

use Beeralex\Catalog\Repository\ProductsRepository as BaseRepository;
use Beeralex\Core\Service\FileService;

class ProductsRepository extends BaseRepository
{
    public function getProducts(array $productIds, bool $onlyActive = true): array
    {
        $products = parent::getProducts($productIds, $onlyActive);
        
        // Добавляем обработку картинок
        $pictureIds = [];
        foreach ($products as $product) {
            if ($product['PREVIEW_PICTURE']) {
                $pictureIds[] = (int)$product['PREVIEW_PICTURE'];
            }
        }
        
        $fileService = service(FileService::class);
        $paths = $fileService->getPathByIds($pictureIds);
        
        foreach ($products as &$product) {
            if (isset($paths[$product['PREVIEW_PICTURE']])) {
                $product['PREVIEW_PICTURE_SRC'] = $paths[$product['PREVIEW_PICTURE']];
            }
        }
        
        return $products;
    }
}
```

### Регистрация в DI

Модуль уже регистрирует стандартные репозитории в своем `.settings.php`. Для переопределения создайте файл `/local/.settings_extra.php` в корне проекта:

```php
use Beeralex\Catalog\Enum\DIServiceKey;
use App\Repository\ProductsRepository;

return [
    'services' => [
        'value' => [
            // Переопределяем стандартный репозиторий модуля своей реализацией
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

После этого все обращения через контракт будут использовать вашу реализацию вместо стандартной.

---

## Контракты (Interfaces)

### ProductRepositoryContract

Интерфейс для репозитория товаров.

### OfferRepositoryContract

Интерфейс для репозитория предложений.

### StoreRepositoryContract

Интерфейс для репозитория складов.

Эти интерфейсы позволяют легко подменять реализации репозиториев через DI контейнер, что упрощает тестирование и поддержку кода.

---

## Рекомендации

1. **Используйте кеширование** для методов с параметром `$cacheTtl`, особенно для блоков типа "Похожие товары", "Новинки"
2. **Фильтруйте на уровне запросов**, а не после получения данных - это эффективнее
3. **Используйте `select`** для выборки только нужных полей, особенно если не требуются цены или склады
4. **Расширяйте, а не изменяйте** базовые репозитории - создавайте свои классы-наследники
5. **Регистрируйте в DI** свои реализации для гибкой замены логики
