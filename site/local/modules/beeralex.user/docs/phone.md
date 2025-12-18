# Работа с телефонами

Документация по классу Phone и работе с телефонными номерами.

## Обзор

Класс `Phone` предоставляет работу с телефонными номерами международного формата:
- Парсинг и валидация номеров
- Форматирование в различных форматах (E164, International, National)
- Определение страны по коду
- Интеграция с libphonenumber-for-php
- Поддержка любых стран мира

## Установка зависимостей

Модуль использует встроенные классы Bitrix для работы с телефонами.
Внутренняя реализация основана на `giggsey/libphonenumber-for-php`, которая уже включена в Bitrix Framework.

Дополнительная установка не требуется.

## Класс Phone

Неизменяемый value object для представления телефонного номера.

### Создание экземпляра

#### `fromString(string $phone, ?string $defaultRegion = null): self`

Создает Phone из строки.

```php
use Beeralex\User\DTO\Phone;

// С кодом страны
$phone = Phone::fromString('+79991234567');

// Без кода (с указанием региона по умолчанию)
$phone = Phone::fromString('9991234567', 'RU');

// Международный формат
$phone = Phone::fromString('+1 (555) 123-4567', 'US');
```

**Поддерживаемые форматы:**
- `+79991234567`
- `+7 999 123 45 67`
- `+7 (999) 123-45-67`
- `8 (999) 123-45-67` (при defaultRegion = 'RU')
- `9991234567` (при defaultRegion = 'RU')

#### Обработка ошибок

```php
try {
    $phone = Phone::fromString('invalid');
} catch (\InvalidArgumentException $e) {
    echo "Некорректный номер: " . $e->getMessage();
}
```

### Методы форматирования

#### `formatE164(): string`

Возвращает номер в формате E164 (международный стандарт).

```php
$phone = Phone::fromString('+7 999 123 45 67');
echo $phone->formatE164();
// +79991234567
```

**Использование:** Хранение в БД, API запросы, уникальная идентификация.

#### `formatInternational(): string`

Возвращает номер в международном формате с пробелами.

```php
$phone = Phone::fromString('+79991234567');
echo $phone->formatInternational();
// +7 999 123 45 67
```

**Использование:** Отображение пользователю, отправка SMS.

#### `formatNational(): string`

Возвращает номер в национальном формате.

```php
$phone = Phone::fromString('+79991234567');
echo $phone->formatNational();
// 8 (999) 123-45-67
```

**Использование:** Отображение в формате привычном для страны.

### Методы получения данных

#### `getCountryCode(): int`

Возвращает код страны (без +).

```php
$phone = Phone::fromString('+79991234567');
echo $phone->getCountryCode();
// 7
```

#### `getRegionCode(): string`

Возвращает ISO код региона (страны).

```php
$phone = Phone::fromString('+79991234567');
echo $phone->getRegionCode();
// RU
```

#### `getNationalNumber(): string`

Возвращает национальный номер (без кода страны).

```php
$phone = Phone::fromString('+79991234567');
echo $phone->getNationalNumber();
// 9991234567
```

### Методы проверки

#### `isValid(): bool`

Проверяет валидность номера.

```php
$phone = Phone::fromString('+79991234567');
if ($phone->isValid()) {
    echo "Номер валиден";
}
```

#### `equals(Phone $other): bool`

Сравнивает два номера.

```php
$phone1 = Phone::fromString('+79991234567');
$phone2 = Phone::fromString('8 (999) 123-45-67', 'RU');

if ($phone1->equals($phone2)) {
    echo "Номера идентичны";
}
```

### Строковое представление

#### `__toString(): string`

Возвращает E164 формат при приведении к строке.

```php
$phone = Phone::fromString('+7 999 123 45 67');
echo $phone;
// +79991234567

// В массиве
$data = [
    'phone' => $phone
];
echo json_encode($data);
// {"phone":"+79991234567"}
```

## Работа с БД

### Сохранение номеров

Всегда сохраняйте в формате E164:

```php
use Beeralex\User\DTO\Phone;
use Bitrix\Main\Type\DateTime;

$phone = Phone::fromString($_POST['phone'], 'RU');

$user = new CUser;
$user->Update($userId, [
    'PERSONAL_PHONE' => $phone->formatE164(), // +79991234567
    'TIMESTAMP_X' => new DateTime(),
]);
```

### Загрузка номеров

```php
$userData = CUser::GetByID($userId)->Fetch();
$phoneStr = $userData['PERSONAL_PHONE'];

if ($phoneStr) {
    $phone = Phone::fromString($phoneStr);
    echo $phone->formatInternational();
}
```

## Валидация в формах

### Компонент регистрации

```php
use Beeralex\User\DTO\Phone;

// Валидация
if ($phone = $this->request->getPost('phone')) {
    try {
        $phoneObj = Phone::fromString($phone, 'RU');
        
        if (!$phoneObj->isValid()) {
            $this->arResult['ERRORS'][] = 'Некорректный номер телефона';
        } else {
            $this->arResult['PHONE'] = $phoneObj->formatE164();
        }
    } catch (\InvalidArgumentException $e) {
        $this->arResult['ERRORS'][] = 'Некорректный формат телефона';
    }
}
```

### JavaScript валидация

```javascript
// Клиентская проверка формата
function validatePhone(phone) {
    // Простая проверка для России
    const regex = /^(\+7|8)?[\s-]?\(?[0-9]{3}\)?[\s-]?[0-9]{3}[\s-]?[0-9]{2}[\s-]?[0-9]{2}$/;
    return regex.test(phone);
}

// Форматирование при вводе
function formatPhoneInput(input) {
    let value = input.value.replace(/\D/g, '');
    
    if (value.startsWith('8')) {
        value = '7' + value.slice(1);
    }
    
    let formatted = '+7';
    if (value.length > 1) {
        formatted += ' (' + value.slice(1, 4);
    }
    if (value.length >= 5) {
        formatted += ') ' + value.slice(4, 7);
    }
    if (value.length >= 8) {
        formatted += '-' + value.slice(7, 9);
    }
    if (value.length >= 10) {
        formatted += '-' + value.slice(9, 11);
    }
    
    input.value = formatted;
}

document.querySelector('#phone').addEventListener('input', function(e) {
    formatPhoneInput(e.target);
});
```

## REST API

### Отправка телефона в API

```javascript
const phone = document.querySelector('#phone').value;

const response = await fetch('/api/v1/auth/register/', {
    method: 'POST',
    headers: {'Content-Type': 'application/json'},
    body: JSON.stringify({
        type: 'phone',
        phone: phone, // +7 (999) 123-45-67 или любой формат
        firstName: 'Иван',
        lastName: 'Иванов'
    })
});
```

### Обработка в контроллере

```php
use Beeralex\User\DTO\Phone;
use Beeralex\User\DTO\AuthCredentialsDto;

class AuthController extends ApiController
{
    public function registerAction()
    {
        $data = $this->getJsonPayload();
        
        // Нормализация телефона
        try {
            $phone = Phone::fromString($data['phone'], 'RU');
        } catch (\InvalidArgumentException $e) {
            return $this->error('Некорректный номер телефона');
        }
        
        $credentials = new AuthCredentialsDto(
            type: 'phone',
            phone: $phone->formatE164(),
            firstName: $data['firstName'],
            lastName: $data['lastName']
        );
        
        // Регистрация
        $authService = service(AuthService::class);
        $result = $authService->register($credentials);
        
        return $this->handleAuthResult($result);
    }
}
```

## PhoneAuthenticator

Аутентификатор с SMS кодами использует Phone для работы.

```php
use Beeralex\User\Auth\Authenticator\PhoneAuthenticator;
use Beeralex\User\DTO\Phone;

$authenticator = service(PhoneAuthenticator::class);

// Отправка кода
$phone = Phone::fromString('+79991234567');
$result = $authenticator->sendCode($phone);

if ($result->isSuccess()) {
    echo "Код отправлен на номер: " . $phone->formatInternational();
}

// Проверка кода
$result = $authenticator->verifyCode($phone, '1234');
```

## Интеграция с репозиторием

### UserRepository

```php
use Beeralex\User\Repository\UserRepositoryContract;
use Beeralex\User\DTO\Phone;

$userRepo = service(UserRepositoryContract::class);

// Поиск по телефону
$phone = Phone::fromString('+79991234567');
$user = $userRepo->getByPhone($phone);

if ($user) {
    echo "Найден пользователь: " . $user->getFullName();
}
```

**Внутри репозитория:**

```php
public function getByPhone(Phone $phone): ?User
{
    // Поиск в E164 формате
    $filter = ['PERSONAL_PHONE' => $phone->formatE164()];
    
    $userData = CUser::GetList(
        $by = 'ID',
        $order = 'ASC',
        $filter
    )->Fetch();
    
    return $userData ? $this->factory->createFromArray($userData) : null;
}
```

## Примеры

### Форма ввода телефона

```html
<form id="phone-form">
    <div class="form-group">
        <label for="phone">Телефон</label>
        <input 
            type="tel" 
            id="phone" 
            name="phone" 
            class="form-control"
            placeholder="+7 (___) ___-__-__"
            required
        >
        <small class="form-text text-muted">
            Введите номер в формате +7 (999) 123-45-67
        </small>
    </div>
    
    <button type="submit" class="btn btn-primary">
        Получить код
    </button>
</form>

<script>
document.querySelector('#phone-form').addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const phone = document.querySelector('#phone').value;
    
    const response = await fetch('/api/v1/auth/send-code/', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({ phone })
    });
    
    const data = await response.json();
    
    if (data.status === 'success') {
        // Показать форму ввода кода
        showCodeForm();
    } else {
        alert(data.errors[0]);
    }
});
</script>
```

### Отображение телефона в профиле

```php
use Beeralex\User\Repository\UserRepositoryContract;
use Beeralex\User\DTO\Phone;

$userRepo = service(UserRepositoryContract::class);
$user = $userRepo->getCurrentUser();

$phone = $user->getPhone(); // Возвращает Phone объект

if ($phone) {
    echo "Телефон: " . $phone->formatInternational();
    echo "Страна: " . $phone->getRegionCode();
}
```

### Валидатор телефона

```php
use Beeralex\User\DTO\Phone;

class PhoneValidator
{
    public function validate(string $phone, string $region = 'RU'): array
    {
        $errors = [];
        
        try {
            $phoneObj = Phone::fromString($phone, $region);
            
            if (!$phoneObj->isValid()) {
                $errors[] = 'Номер телефона не валиден';
            }
            
            // Дополнительные проверки
            if ($phoneObj->getRegionCode() !== $region) {
                $errors[] = "Ожидается номер региона {$region}";
            }
            
        } catch (\InvalidArgumentException $e) {
            $errors[] = 'Некорректный формат телефона';
        }
        
        return $errors;
    }
}
```

### Миграция существующих номеров

```php
// Скрипт для нормализации телефонов в БД
use Beeralex\User\DTO\Phone;

$users = CUser::GetList(
    $by = 'ID',
    $order = 'ASC',
    ['!PERSONAL_PHONE' => false]
);

$updated = 0;
$errors = 0;

while ($user = $users->Fetch()) {
    $oldPhone = $user['PERSONAL_PHONE'];
    
    try {
        $phone = Phone::fromString($oldPhone, 'RU');
        $normalized = $phone->formatE164();
        
        if ($normalized !== $oldPhone) {
            $userObj = new CUser;
            $userObj->Update($user['ID'], [
                'PERSONAL_PHONE' => $normalized
            ]);
            
            echo "Обновлен: {$oldPhone} -> {$normalized}\n";
            $updated++;
        }
    } catch (\InvalidArgumentException $e) {
        echo "Ошибка для ID {$user['ID']}: {$oldPhone}\n";
        $errors++;
    }
}

echo "\nОбновлено: {$updated}, Ошибок: {$errors}\n";
```

### Фильтрация по стране

```php
use Beeralex\User\DTO\Phone;

$users = CUser::GetList(
    $by = 'ID',
    $order = 'ASC',
    ['!PERSONAL_PHONE' => false]
);

$russianUsers = [];

while ($user = $users->Fetch()) {
    try {
        $phone = Phone::fromString($user['PERSONAL_PHONE']);
        
        if ($phone->getRegionCode() === 'RU') {
            $russianUsers[] = $user;
        }
    } catch (\InvalidArgumentException $e) {
        continue;
    }
}

echo "Пользователей с российскими номерами: " . count($russianUsers);
```

## Поддерживаемые страны

Библиотека libphonenumber поддерживает все страны мира. Примеры:

```php
// Россия
Phone::fromString('+79991234567')->getRegionCode(); // RU

// США
Phone::fromString('+1 555 123 4567')->getRegionCode(); // US

// Германия
Phone::fromString('+49 30 12345678')->getRegionCode(); // DE

// Казахстан
Phone::fromString('+77012345678')->getRegionCode(); // KZ

// Украина
Phone::fromString('+380501234567')->getRegionCode(); // UA
```

## Тестирование

```php
use PHPUnit\Framework\TestCase;
use Beeralex\User\DTO\Phone;

class PhoneTest extends TestCase
{
    public function testCreateFromString()
    {
        $phone = Phone::fromString('+79991234567');
        
        $this->assertEquals('+79991234567', $phone->formatE164());
        $this->assertEquals('+7 999 123 45 67', $phone->formatInternational());
        $this->assertEquals('RU', $phone->getRegionCode());
        $this->assertTrue($phone->isValid());
    }
    
    public function testCreateWithDefaultRegion()
    {
        $phone = Phone::fromString('9991234567', 'RU');
        
        $this->assertEquals('+79991234567', $phone->formatE164());
    }
    
    public function testInvalidPhone()
    {
        $this->expectException(\InvalidArgumentException::class);
        Phone::fromString('invalid');
    }
    
    public function testEquals()
    {
        $phone1 = Phone::fromString('+79991234567');
        $phone2 = Phone::fromString('8 (999) 123-45-67', 'RU');
        
        $this->assertTrue($phone1->equals($phone2));
    }
}
```

## Навигация

- [← JWT токены](jwt-tokens.md)
- [Социальная аутентификация →](social-auth.md)
