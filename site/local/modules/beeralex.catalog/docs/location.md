# Система Location (Геолокация)

Модуль `beeralex.catalog` включает продвинутую систему определения местоположения, которая интегрируется с внешними API (например, DaData) и преобразует адреса в коды локаций Bitrix.

## Архитектура

Система построена на трех основных компонентах:

1. **API Client** - взаимодействие с внешними сервисами геолокации
2. **Parser** - разбор данных от API и извлечение нужной информации
3. **Resolver** - преобразование данных API в коды локаций Bitrix

```
Адрес/Координаты → API Client → Parser → Resolver → Код локации Bitrix
```

---

## LocationDTO

### Описание

Data Transfer Object для передачи координат.

### Свойства

```php
public float $latitude;   // Широта
public float $longitude;  // Долгота
```

### Использование

```php
use Beeralex\Catalog\Dto\LocationDTO;

$location = LocationDTO::make([
    'LATITUDE' => 55.753215,
    'LONGITUDE' => 37.622504
]);

echo $location->latitude;  // 55.753215
echo $location->longitude; // 37.622504
```

---

## Контракты (Interfaces)

### LocationApiClientContract

Интерфейс для клиентов API геолокации.

```php
interface LocationApiClientContract
{
    /**
     * Получает подсказки адресов по запросу
     */
    public function suggestAddress(string $query, int $count = 5): array;
    
    /**
     * Определяет адреса по координатам
     */
    public function geolocate(
        float $lat,
        float $lon,
        int $radius = 100,
        int $count = 3
    ): array;
    
    /**
     * Возвращает парсер для данных API
     */
    public function getParser(): ?LocationDataParserContract;
}
```

### LocationDataParserContract

Интерфейс для парсеров данных геолокации.

```php
interface LocationDataParserContract
{
    /**
     * Парсит данные и возвращает варианты названий для поиска
     * 
     * @return array [
     *     0 => array settlement варианты (населенный пункт),
     *     1 => array city варианты (город),
     *     2 => array area варианты (район),
     *     3 => array region варианты (регион)
     * ]
     */
    public function parse(array $suggestions): array;
}
```

### BitrixLocationResolverContract

Интерфейс для резолвера локаций Bitrix.

```php
interface BitrixLocationResolverContract
{
    /**
     * Возвращает данные местоположения из Bitrix по адресу или координатам
     * 
     * @param string|LocationDTO $location Адрес или координаты
     * @return array|null ['city' => ..., 'code' => ..., 'area' => ..., 'region' => ...]
     */
    public function getBitrixLocationByAddress(string|LocationDTO $location): ?array;
}
```

---

## DadataService

### Описание

Реализация клиента для API сервиса DaData (dadata.ru). Предоставляет подсказки адресов и геолокацию по координатам.

### Конструктор

```php
public function __construct(
    string $apiKey,      // API ключ DaData
    string $secretKey    // Секретный ключ DaData
)
```

### Методы

#### suggestAddress()

Получает подсказки адресов по текстовому запросу.

```php
public function suggestAddress(string $query, int $count = 5): array
```

**Пример:**

```php
$dadata = new DadataService($apiKey, $secretKey);
$suggestions = $dadata->suggestAddress('Москва, Красная', 5);

/*
[
    [
        'value' => 'г Москва, пл Красная',
        'data' => [
            'city' => 'Москва',
            'region' => 'Москва',
            ...
        ]
    ],
    ...
]
*/
```

#### geolocate()

Определяет адреса по координатам.

```php
public function geolocate(
    float $lat,
    float $lon,
    int $radius = 100,    // Радиус поиска в метрах
    int $count = 3        // Количество результатов
): array
```

**Пример:**

```php
$suggestions = $dadata->geolocate(55.753215, 37.622504, 100, 3);
// Вернет ближайшие адреса к Красной площади
```

#### getParser()

Возвращает парсер для данных DaData.

```php
public function getParser(): ?LocationDataParserContract
```

### Регистрация в DI

```php
use Beeralex\Catalog\Location\Service\DadataService;
use Beeralex\Catalog\Location\Contracts\LocationApiClientContract;

return [
    'services' => [
        'value' => [
            LocationApiClientContract::class => [
                'constructor' => static function() {
                    $options = service(\Beeralex\Catalog\Options::class);
                    return new DadataService(
                        $options->apiKey,
                        $options->secretKey
                    );
                }
            ]
        ]
    ]
];
```

---

## DadataLocationParser

### Описание

Парсер для данных, полученных от DaData API. Извлекает названия населенных пунктов, городов, районов и регионов для дальнейшего поиска в Bitrix.

### Метод parse()

```php
public function parse(array $suggestions): array
```

**Входные данные:** массив подсказок от DaData API.

**Возвращает:**

```php
[
    0 => ['Москва'],              // settlement варианты
    1 => ['Москва', 'Moscow'],    // city варианты
    2 => [],                       // area варианты
    3 => ['Москва', 'Московская'] // region варианты
]
```

---

## BitrixLocationResolver

### Описание

Главный класс для определения локации в Bitrix. Использует API клиент (например, DaData) для получения данных о местоположении, а затем ищет соответствующий код локации в справочнике Bitrix.

### Особенности

- **Кеширование результатов** на 3600000 секунд (1000 часов)
- **Приоритетный поиск**: сначала ищет по населенному пункту, потом по городу, району, региону
- **Умное сопоставление**: пытается найти наиболее точное совпадение по региону и району
- **Обработка ошибок**: безопасно обрабатывает исключения и возвращает null

### Конструктор

```php
public function __construct(
    protected readonly LocationApiClientContract $client,
    protected readonly LocationService $locationService
)
```

### Метод getBitrixLocationByAddress()

Возвращает данные местоположения из Bitrix по адресу или координатам.

```php
public function getBitrixLocationByAddress(string|LocationDTO $location): ?array
```

**Параметры:**
- `$location` - строка адреса или объект `LocationDTO` с координатами

**Возвращает:**

```php
[
    'city' => 'Москва',           // Название города
    'code' => '0000073738',       // Код локации в Bitrix
    'area' => null,               // Название района (если есть)
    'region' => 'Москва'          // Название региона
]
```

Возвращает `null`, если локация не найдена.

**Примеры:**

```php
use Beeralex\Catalog\Location\Contracts\BitrixLocationResolverContract;

$resolver = service(BitrixLocationResolverContract::class);

// Поиск по адресу
$location = $resolver->getBitrixLocationByAddress('Москва, Красная площадь, 1');
/*
[
    'city' => 'Москва',
    'code' => '0000073738',
    'area' => null,
    'region' => 'Москва'
]
*/

// Поиск по координатам
$coords = LocationDTO::make([
    'LATITUDE' => 55.753215,
    'LONGITUDE' => 37.622504
]);
$location = $resolver->getBitrixLocationByAddress($coords);
```

### Алгоритм работы

1. **Получение данных от API**
   - Если передан адрес → вызов `suggestAddress()`
   - Если координаты → вызов `geolocate()`

2. **Парсинг данных**
   - Извлечение вариантов названий (settlement, city, area, region)

3. **Приоритетный поиск в Bitrix**
   - Поиск по населенному пункту (settlement)
   - Если не найдено → поиск по городу (city)
   - Если не найдено → поиск по району (area)
   - Если не найдено → поиск по региону (region)

4. **Уточнение по региону и району**
   - Среди найденных локаций ищет наиболее точное совпадение
   - Проверяет совпадение региона и района в иерархии локации

5. **Кеширование**
   - Результат кешируется на длительный срок

### Внутренние методы

#### getVariantsFromLocation()

Получает варианты названий из данных API.

```php
private function getVariantsFromLocation(string|LocationDTO $location): ?array
```

#### searchPriority()

Выполняет приоритетный поиск по группам вариантов.

```php
private function searchPriority(array $groups): array
```

#### searchInBitrix()

Ищет локации в справочнике Bitrix по вариантам названий.

```php
private function searchInBitrix(array $variants): array
```

#### matchRegionAndArea()

Уточняет результат поиска по региону и району.

```php
private function matchRegionAndArea(
    array $items,
    array $regionVariants,
    array $areaVariants
): ?array
```

---

## Использование в компонентах

### Пример в компоненте оформления заказа

```php
use Beeralex\Catalog\Location\Contracts\BitrixLocationResolverContract;

class CheckoutComponent extends CBitrixComponent
{
    protected ?BitrixLocationResolverContract $locationResolver = null;
    
    public function executeComponent()
    {
        $this->locationResolver = service(BitrixLocationResolverContract::class);
        
        // Получаем адрес от пользователя
        $address = $_POST['address'] ?? '';
        
        if ($address) {
            $location = $this->locationResolver->getBitrixLocationByAddress($address);
            
            if ($location) {
                // Устанавливаем локацию в заказ
                $this->arResult['LOCATION_CODE'] = $location['code'];
                $this->arResult['CITY'] = $location['city'];
            }
        }
        
        $this->includeComponentTemplate();
    }
}
```

### Пример с координатами

```php
use Beeralex\Catalog\Dto\LocationDTO;
use Beeralex\Catalog\Location\Contracts\BitrixLocationResolverContract;

// Получаем координаты от пользователя (например, через HTML5 Geolocation API)
$lat = (float)$_POST['latitude'];
$lon = (float)$_POST['longitude'];

$locationDTO = LocationDTO::make([
    'LATITUDE' => $lat,
    'LONGITUDE' => $lon
]);

$resolver = service(BitrixLocationResolverContract::class);
$location = $resolver->getBitrixLocationByAddress($locationDTO);

if ($location) {
    echo "Вы находитесь в городе: " . $location['city'];
    echo "Код локации: " . $location['code'];
}
```

---

## Настройка

### Получение API ключей DaData

1. Зарегистрируйтесь на [dadata.ru](https://dadata.ru)
2. Получите API ключ и секретный ключ
3. Добавьте их в настройки модуля

### Настройка через админку

В административной панели Bitrix → Настройки → Настройки модулей → beeralex.catalog:

- **API_KEY** - API ключ DaData
- **SECRET_KEY** - Секретный ключ DaData

### Программная настройка

```php
use Beeralex\Catalog\Options;

$options = service(Options::class);
$apiKey = $options->apiKey;
$secretKey = $options->secretKey;
```

---

## Кеширование

Результаты поиска локаций кешируются на **1000 часов** (3600000 секунд), так как справочник локаций Bitrix меняется редко.

Кеш хранится в директории: `bitrix/cache/beeralex.catalog/location/`

Для очистки кеша можно использовать:

```php
\Bitrix\Main\Data\Cache::clearCache(true, 'beeralex.catalog/location');
```

---

## Создание кастомного API клиента

Вы можете создать свой клиент для другого API (например, Google Maps, Yandex Maps):

```php
namespace App\Location;

use Beeralex\Catalog\Location\Contracts\LocationApiClientContract;
use Beeralex\Catalog\Location\Contracts\LocationDataParserContract;

class GoogleMapsClient implements LocationApiClientContract
{
    public function __construct(private string $apiKey) {}
    
    public function suggestAddress(string $query, int $count = 5): array
    {
        // Ваша реализация через Google Maps API
        $url = "https://maps.googleapis.com/maps/api/geocode/json";
        // ...
        return $results;
    }
    
    public function geolocate(float $lat, float $lon, int $radius = 100, int $count = 3): array
    {
        // Ваша реализация
        return $results;
    }
    
    public function getParser(): ?LocationDataParserContract
    {
        return new GoogleMapsParser();
    }
}
```

Затем зарегистрируйте в DI:

```php
use App\Location\GoogleMapsClient;
use Beeralex\Catalog\Location\Contracts\LocationApiClientContract;

return [
    'services' => [
        'value' => [
            LocationApiClientContract::class => [
                'constructor' => static function() {
                    return new GoogleMapsClient('YOUR_GOOGLE_API_KEY');
                }
            ]
        ]
    ]
];
```

---

## Обработка ошибок

Система безопасно обрабатывает ошибки и исключения:

```php
try {
    $location = $resolver->getBitrixLocationByAddress($address);
    if ($location === null) {
        // Локация не найдена
        echo "К сожалению, не удалось определить ваше местоположение";
    }
} catch (\Throwable $e) {
    // Ошибка не выбрасывается наружу, но можно логировать
    \Beeralex\Catalog\log("Location error: " . $e->getMessage());
}
```

---

## Рекомендации

1. **Используйте кеширование** - не делайте повторные запросы к API для одного и того же адреса
2. **Обрабатывайте null** - метод может вернуть null, если локация не найдена
3. **Проверяйте лимиты API** - DaData имеет ограничения на количество запросов
4. **Тестируйте на реальных данных** - проверьте работу на адресах вашего региона
5. **Логируйте ошибки** - используйте функцию `log()` для отладки проблем с геолокацией
