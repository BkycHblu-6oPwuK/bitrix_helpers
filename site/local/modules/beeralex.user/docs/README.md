# Документация модуля beeralex.user

Модуль управления пользователями и аутентификацией для Bitrix Framework с поддержкой множественных методов авторизации, JWT токенов и социальных сетей.

## Содержание документации

1. **[Быстрый старт](getting-started.md)** - установка, настройка, первые шаги
2. **[User, Repository, Service](user-entity.md)** - работа с сущностью пользователя
3. **[Система аутентификации](authentication.md)** - AuthManager, AuthService, аутентификаторы
4. **[JWT токены](jwt-tokens.md)** - JwtTokenManager, access/refresh токены
5. **[Работа с телефонами](phone.md)** - класс Phone, форматирование
6. **[Социальная авторизация](social-auth.md)** - Telegram, OAuth провайдеры
7. **[Расширение функционала](extending.md)** - создание собственных аутентификаторов

## Обзор модуля

**beeralex.user** — комплексная система управления пользователями с поддержкой:

### 🔐 Аутентификация
- Email + пароль
- Телефон + SMS код
- Социальные сети (Telegram, OAuth провайдеры)
- JWT токены (access + refresh)
- Сессии с отслеживанием устройств

### 👤 Управление пользователями
- User entity с типизированными методами
- UserRepository для работы с БД
- UserService для бизнес-логики
- UserBuilder для создания пользователей
- Phone класс для работы с номерами

### 🏗️ Архитектура
- Contract-based design
- Strategy pattern для аутентификаторов
- Factory pattern для создания сущностей
- Dependency Injection через DI-контейнер

### ⚙️ Возможности
- Смена пароля
- Восстановление пароля
- Обновление профиля
- Валидация уникальности email/телефона
- Управление сессиями
- Ротация токенов

## Быстрый пример

```php
use Beeralex\User\Auth\AuthService;
use Beeralex\User\Auth\AuthCredentialsDto;

// Авторизация по email
$authService = service(AuthService::class);

$credentials = new AuthCredentialsDto(
    type: 'email',
    email: 'user@example.com',
    password: 'secret123'
);

$result = $authService->login($credentials);

if ($result->isSuccess()) {
    $data = $result->getData();
    echo "User ID: {$data['userId']}";
    echo "Access Token: {$data['accessToken']}";
}
```

## Основные компоненты

### User Entity
Основной класс пользователя с типизированными getter'ами:
```php
$user = service(UserRepositoryContract::class)->getCurrentUser();
echo $user->getName();
echo $user->getEmail();
echo $user->getPhone()?->formatE164();
```

### AuthManager
Управляет процессом аутентификации через различные провайдеры:
```php
$authManager = service(AuthManager::class);
$result = $authManager->authenticate('email', $credentials);
$result = $authManager->register('phone', $credentials);
```

### AuthService
Высокоуровневый сервис для авторизации с JWT:
```php
$authService = service(AuthService::class);
$result = $authService->login($credentials);
$result = $authService->register($credentials);
$result = $authService->refreshTokens($refreshToken);
```

### JwtTokenManager
Управление JWT токенами:
```php
$jwtManager = service(JwtTokenManager::class);
$result = $jwtManager->generateTokenPair($userId);
$result = $jwtManager->validateAccessToken($token);
```

## Диаграмма архитектуры

```
┌─────────────────────────────────────────────────────────┐
│                     AuthService                         │
│          (высокоуровневый API для фронтенда)            │
└──────────────────┬──────────────────────────────────────┘
                   │
        ┌──────────┴──────────┐
        │                     │
   ┌────▼─────┐         ┌─────▼──────────┐
   │AuthManager│         │JwtTokenManager│
   │(стратегии)│         │  (JWT токены) │
   └────┬──────┘         └───────────────┘
        │
        ├──────┬──────────┬──────────┐
        │      │          │          │
   ┌────▼───┐ ▼          ▼          ▼
   │Email   │Phone    Social   Empty
   │Auth    │Auth     Auth     Auth
   └────┬───┘
        │
   ┌────▼──────────┐
   │UserRepository │
   │UserService    │
   │User Entity    │
   └───────────────┘
```

## Зависимости

- PHP 8.2+
- Bitrix Framework 25.0+ (рекомендуемая для php 8.2)
- `beeralex.core` — базовые абстракции
- `firebase/php-jwt` — JWT токены

## Навигация

- [← Назад к списку модулей](../../README.md)
- [Быстрый старт →](getting-started.md)
