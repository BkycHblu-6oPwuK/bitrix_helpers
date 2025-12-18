# Примеры использования

Практические примеры использования модуля `beeralex.catalog` в реальных сценариях.

---

## Каталог товаров

### Список товаров в разделе

```php
use Beeralex\Catalog\Service\CatalogService;
use Beeralex\Catalog\Contracts\ProductRepositoryContract;

// Получаем ID товаров раздела
$productRepo = service(ProductRepositoryContract::class);
$productIds = $productRepo->getAvailableProductIds([
    'IBLOCK_SECTION_ID' => 10,
    'ACTIVE' => 'Y'
]);

// Получаем товары с предложениями и скидками
$catalogService = service(CatalogService::class);
$products = $catalogService->getProductsWithOffers(
    array_slice($productIds, 0, 20), // Первые 20 товаров
    true,  // Только в наличии
    true   // С применением скидок
);

// Выводим
foreach ($products as $product) {
    echo "<div class='product'>";
    echo "<h3>{$product['NAME']}</h3>";
    echo "<a href='{$product['DETAIL_PAGE_URL']}'>";
    
    // Предложения
    foreach ($product['OFFERS'] as $offer) {
        $price = $offer['PRICE'][0] ?? null;
        if ($price) {
            echo "<div class='offer'>";
            echo "{$offer['NAME']}: ";
            echo number_format($price['PRICE'], 0, '', ' ') . " руб.";
            echo "</div>";
        }
    }
    echo "</a></div>";
}
```

### Детальная страница товара

```php
use Beeralex\Catalog\Contracts\ProductRepositoryContract;

$productId = 123;
$productRepo = service(ProductRepositoryContract::class);
$product = $productRepo->getProductWithOffers($productId);

if (!$product) {
    LocalRedirect('/404.php');
}

// Просмотренные товары
$viewedIds = $productRepo->getViewedProductsIds($productId);
$viewedProducts = $catalogService->getProductsWithOffers($viewedIds, true, true);

// Похожие товары
$similarIds = $productRepo->getSameProductsIds(
    $productId, 
    $product['IBLOCK_SECTION_ID'], 
    10, 
    3600
);
$similarProducts = $catalogService->getProductsWithOffers($similarIds, true, true);
```

### Новинки и хиты

```php
use Beeralex\Catalog\Contracts\ProductRepositoryContract;
use Beeralex\Catalog\Service\CatalogService;

$productRepo = service(ProductRepositoryContract::class);
$catalogService = service(CatalogService::class);

// Новинки за последние 2 месяца
$newIds = $productRepo->getNewProductsIds(12, 3600, 2);
$newProducts = $catalogService->getProductsWithOffers($newIds, true, true);

// Хиты продаж (популярные)
$popularIds = $productRepo->getPopularProductsIds(12, 3600);
$popularProducts = $catalogService->getProductsWithOffers($popularIds, true, true);
```

---

## Корзина

### AJAX добавление в корзину

```php
// ajax/add_to_basket.php
require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php';

use Beeralex\Catalog\Service\Basket\BasketFactory;

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die(json_encode(['success' => false, 'error' => 'Method not allowed']));
}

$offerId = (int)($_POST['offer_id'] ?? 0);
$quantity = (int)($_POST['quantity'] ?? 1);

if (!$offerId) {
    die(json_encode(['success' => false, 'error' => 'Не указан товар']));
}

$basketFactory = service(BasketFactory::class);
$basketService = $basketFactory->createBasketServiceForCurrentUser();

$result = $basketService->increment($offerId, $quantity);

if ($result->isSuccess()) {
    $data = $basketService->getBasketData();
    echo json_encode([
        'success' => true,
        'message' => 'Товар добавлен в корзину',
        'basket' => [
            'count' => $data['SUMMARY']['TOTAL_QUANTITY'],
            'total' => $data['SUMMARY']['TOTAL_PRICE'],
            'totalFormatted' => $data['SUMMARY']['TOTAL_PRICE_FORMATTED']
        ]
    ]);
} else {
    $errors = array_map(fn($e) => $e->getMessage(), $result->getErrors());
    echo json_encode([
        'success' => false,
        'error' => implode(', ', $errors)
    ]);
}
```

### Страница корзины

```php
// basket.php
use Beeralex\Catalog\Service\Basket\BasketFactory;

$basketFactory = service(BasketFactory::class);
$basketService = $basketFactory->createBasketServiceForCurrentUser();
$basketData = $basketService->getBasketData();

?>
<div class="basket-page">
    <h1>Корзина</h1>
    
    <?php if (empty($basketData['ITEMS'])): ?>
        <p>Ваша корзина пуста</p>
    <?php else: ?>
        <div class="basket-items">
            <?php foreach ($basketData['ITEMS'] as $item): ?>
                <div class="basket-item" data-offer-id="<?= $item['PRODUCT_ID'] ?>">
                    <div class="item-name"><?= htmlspecialchars($item['NAME']) ?></div>
                    <div class="item-price">
                        <?php if ($item['OLD_PRICE']): ?>
                            <span class="old-price"><?= $item['OLD_PRICE_FORMATTED'] ?></span>
                            <span class="discount">-<?= $item['DISCOUNT_PERCENT'] ?>%</span>
                        <?php endif; ?>
                        <span class="price"><?= $item['PRICE_FORMATTED'] ?></span>
                    </div>
                    <div class="item-quantity">
                        <input type="number" 
                               value="<?= $item['QUANTITY'] ?>" 
                               min="1" 
                               class="quantity-input"
                               data-offer-id="<?= $item['PRODUCT_ID'] ?>">
                    </div>
                    <div class="item-total"><?= $item['FULL_PRICE_FORMATTED'] ?></div>
                    <button class="remove-btn" data-offer-id="<?= $item['PRODUCT_ID'] ?>">Удалить</button>
                </div>
            <?php endforeach; ?>
        </div>
        
        <div class="basket-summary">
            <?php if ($basketData['COUPON']): ?>
                <div class="coupon-applied">
                    Применен купон: <strong><?= htmlspecialchars($basketData['COUPON']) ?></strong>
                </div>
            <?php endif; ?>
            
            <?php if ($basketData['SUMMARY']['TOTAL_DISCOUNT'] > 0): ?>
                <div class="discount">
                    Скидка: <?= $basketData['SUMMARY']['TOTAL_DISCOUNT_FORMATTED'] ?>
                </div>
            <?php endif; ?>
            
            <div class="total">
                Итого: <strong><?= $basketData['SUMMARY']['TOTAL_PRICE_FORMATTED'] ?></strong>
            </div>
            
            <a href="/order/" class="btn-checkout">Оформить заказ</a>
        </div>
    <?php endif; ?>
</div>

<script>
// Обновление количества
document.querySelectorAll('.quantity-input').forEach(input => {
    input.addEventListener('change', function() {
        const offerId = this.dataset.offerId;
        const quantity = parseInt(this.value);
        
        fetch('/ajax/update_quantity.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `offer_id=${offerId}&quantity=${quantity}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    });
});

// Удаление товара
document.querySelectorAll('.remove-btn').forEach(btn => {
    btn.addEventListener('click', function() {
        const offerId = this.dataset.offerId;
        
        fetch('/ajax/remove_from_basket.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: `offer_id=${offerId}`
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                location.reload();
            }
        });
    });
});
</script>
```

### Применение купона

```php
// ajax/apply_coupon.php
use Beeralex\Catalog\Service\Basket\BasketFactory;

$couponCode = trim($_POST['coupon'] ?? '');

if (!$couponCode) {
    die(json_encode(['success' => false, 'error' => 'Введите код купона']));
}

$basketFactory = service(BasketFactory::class);
$basketService = $basketFactory->createBasketServiceForCurrentUser();

$result = $basketService->applyCoupon($couponCode);

if ($result->isSuccess()) {
    $data = $basketService->getBasketData();
    echo json_encode([
        'success' => true,
        'message' => 'Купон применен',
        'coupon' => $data['COUPON'],
        'discount' => $data['SUMMARY']['TOTAL_DISCOUNT_FORMATTED'],
        'total' => $data['SUMMARY']['TOTAL_PRICE_FORMATTED']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Купон недействителен или не может быть применен'
    ]);
}
```

---

## Поиск

### Форма поиска

```php
// search.php
use Beeralex\Catalog\Service\CatalogService;

$query = trim($_GET['q'] ?? '');

if ($query) {
    $catalogService = service(CatalogService::class);
    $searchResult = $catalogService->search($query, 50, 20);
    
    $products = $searchResult['PRODUCTS'];
    $sections = $searchResult['SECTIONS'];
}
?>

<form action="/search/" method="get" class="search-form">
    <input type="text" 
           name="q" 
           value="<?= htmlspecialchars($query) ?>" 
           placeholder="Поиск товаров..."
           required>
    <button type="submit">Найти</button>
</form>

<?php if (isset($products)): ?>
    <div class="search-results">
        <h2>Найдено товаров: <?= count($products) ?></h2>
        
        <?php if (!empty($sections)): ?>
            <div class="found-sections">
                <h3>Разделы:</h3>
                <?php foreach ($sections as $section): ?>
                    <a href="<?= $section['SECTION_PAGE_URL'] ?>">
                        <?= htmlspecialchars($section['NAME']) ?>
                    </a>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        
        <div class="products">
            <?php foreach ($products as $product): ?>
                <div class="product">
                    <a href="<?= $product['DETAIL_PAGE_URL'] ?>">
                        <h3><?= htmlspecialchars($product['NAME']) ?></h3>
                        <?php if (!empty($product['OFFERS'])): ?>
                            <?php $offer = $product['OFFERS'][0]; ?>
                            <?php $price = $offer['PRICE'][0] ?? null; ?>
                            <?php if ($price): ?>
                                <div class="price">
                                    <?= number_format($price['PRICE'], 0, '', ' ') ?> руб.
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
<?php endif; ?>
```

---

## Определение местоположения

### Автоопределение по координатам

```php
// ajax/detect_location.php
use Beeralex\Catalog\Dto\LocationDTO;
use Beeralex\Catalog\Location\Contracts\BitrixLocationResolverContract;

$lat = (float)($_POST['latitude'] ?? 0);
$lon = (float)($_POST['longitude'] ?? 0);

if (!$lat || !$lon) {
    die(json_encode(['success' => false, 'error' => 'Координаты не указаны']));
}

$locationDTO = LocationDTO::make([
    'LATITUDE' => $lat,
    'LONGITUDE' => $lon
]);

$resolver = service(BitrixLocationResolverContract::class);
$location = $resolver->getBitrixLocationByAddress($locationDTO);

if ($location) {
    echo json_encode([
        'success' => true,
        'city' => $location['city'],
        'code' => $location['code'],
        'region' => $location['region']
    ]);
} else {
    echo json_encode([
        'success' => false,
        'error' => 'Не удалось определить местоположение'
    ]);
}
```

### Подсказки адресов

```php
// ajax/suggest_address.php
use Beeralex\Catalog\Location\Contracts\BitrixLocationResolverContract;

$query = trim($_GET['query'] ?? '');

if (!$query) {
    die(json_encode([]));
}

$resolver = service(BitrixLocationResolverContract::class);
$location = $resolver->getBitrixLocationByAddress($query);

if ($location) {
    echo json_encode([
        'city' => $location['city'],
        'code' => $location['code']
    ]);
} else {
    echo json_encode([]);
}
```

### Форма с автоопределением

```html
<div class="location-form">
    <button id="detect-location">Определить мой город</button>
    <input type="text" id="city" placeholder="Или введите город">
    <input type="hidden" id="location-code" name="location_code">
</div>

<script>
document.getElementById('detect-location').addEventListener('click', function() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(function(position) {
            fetch('/ajax/detect_location.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/x-www-form-urlencoded'},
                body: `latitude=${position.coords.latitude}&longitude=${position.coords.longitude}`
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    document.getElementById('city').value = data.city;
                    document.getElementById('location-code').value = data.code;
                    alert('Ваш город: ' + data.city);
                }
            });
        });
    } else {
        alert('Геолокация не поддерживается вашим браузером');
    }
});

// Автодополнение при вводе города
let timeout;
document.getElementById('city').addEventListener('input', function() {
    clearTimeout(timeout);
    const query = this.value;
    
    if (query.length < 3) return;
    
    timeout = setTimeout(() => {
        fetch('/ajax/suggest_address.php?query=' + encodeURIComponent(query))
            .then(r => r.json())
            .then(data => {
                if (data.city) {
                    document.getElementById('location-code').value = data.code;
                }
            });
    }, 500);
});
</script>
```

---

## Компонент оформления заказа

### Упрощенный компонент

```php
// components/custom/checkout/class.php
use Beeralex\Catalog\Service\Basket\BasketFactory;
use Beeralex\Catalog\Service\OrderService;
use Beeralex\Catalog\Location\Contracts\BitrixLocationResolverContract;

class CheckoutComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        // Корзина
        $basketFactory = service(BasketFactory::class);
        $basketService = $basketFactory->createBasketServiceForCurrentUser();
        $this->arResult['BASKET'] = $basketService->getBasketData();
        
        // Если корзина пуста
        if (empty($this->arResult['BASKET']['ITEMS'])) {
            LocalRedirect('/basket/');
        }
        
        // Обработка формы
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->processOrder();
        }
        
        // Получаем свойства заказа
        $orderService = service(OrderService::class);
        $this->arResult['PROPERTIES'] = $orderService->getPropertyList();
        
        $this->includeComponentTemplate();
    }
    
    protected function processOrder()
    {
        // Получаем данные формы
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $phone = $_POST['phone'] ?? '';
        $address = $_POST['address'] ?? '';
        
        // Определяем локацию
        $resolver = service(BitrixLocationResolverContract::class);
        $location = $resolver->getBitrixLocationByAddress($address);
        
        if (!$location) {
            $this->arResult['ERROR'] = 'Не удалось определить адрес доставки';
            return;
        }
        
        // Создаем заказ
        $order = \Bitrix\Sale\Order::create(SITE_ID, $USER->GetID());
        
        // Добавляем товары из корзины
        $basketFactory = service(BasketFactory::class);
        $basket = $basketFactory->createBasketForCurrentUser();
        $order->setBasket($basket);
        
        // Устанавливаем свойства
        $propertyCollection = $order->getPropertyCollection();
        $propertyCollection->getItemByOrderPropertyCode('NAME')->setValue($name);
        $propertyCollection->getItemByOrderPropertyCode('EMAIL')->setValue($email);
        $propertyCollection->getItemByOrderPropertyCode('PHONE')->setValue($phone);
        $propertyCollection->getItemByOrderPropertyCode('LOCATION')->setValue($location['code']);
        
        // Сохраняем заказ
        $result = $order->save();
        
        if ($result->isSuccess()) {
            LocalRedirect('/order/success/?order_id=' . $order->getId());
        } else {
            $this->arResult['ERRORS'] = $result->getErrors();
        }
    }
}
```

---

## REST API интеграция

### Получение товаров через API

```php
// api/products.php
header('Content-Type: application/json');

use Beeralex\Catalog\Service\CatalogService;
use Beeralex\Catalog\Contracts\ProductRepositoryContract;

$sectionId = (int)($_GET['section_id'] ?? 0);
$limit = (int)($_GET['limit'] ?? 20);

$productRepo = service(ProductRepositoryContract::class);
$productIds = $productRepo->getAvailableProductIds([
    'IBLOCK_SECTION_ID' => $sectionId
]);

$catalogService = service(CatalogService::class);
$products = $catalogService->getProductsWithOffers(
    array_slice($productIds, 0, $limit),
    true,
    true
);

echo json_encode([
    'success' => true,
    'data' => $products
]);
```

---

## Рекомендации

1. **Кешируйте результаты** - используйте встроенное кеширование Bitrix для списков товаров
2. **Обрабатывайте ошибки** - всегда проверяйте `Result::isSuccess()`
3. **Используйте транзакции** - для операций с корзиной и заказами
4. **Валидируйте входные данные** - особенно в AJAX обработчиках
5. **Логируйте критичные операции** - для отладки и аудита
