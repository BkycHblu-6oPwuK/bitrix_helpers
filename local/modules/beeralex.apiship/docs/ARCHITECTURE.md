# Архитектура модуля `beeralex.apiship`

Единая интеграция всех служб доставки через агрегатор **ApiShip**.
Один битриксовый обработчик доставки + богатое PHP-API модуля (Service-слой).
Фронт (Nuxt) **вне scope** — модуль чисто бэкенд. Единственный HTTP-эндпоинт —
входящий webhook ApiShip (server-to-server).

## Принципы

- **SDK расширяем только внутри модуля** (`lib/Sdk/`), `vendor/apiship/apiship-sdk-php` не трогаем —
  чтобы `composer update` не затирал доработки.
- **Хранение** — только собственные ORM-таблицы (префикс `beeralex_apiship_`), без HLBlock.
- **Настройки** — `Options extends Beeralex\Core\Config\AbstractOptions` + `.env` для секретов (токен).
- **DI** — штатный `Bitrix\Main\DI\ServiceLocator`, регистрация в `.settings.php`, доступ через `service()`.
- **Автолоад** классов — конвент Bitrix: `beeralex.apiship` → namespace `Beeralex\Apiship`, каталог `lib/`.
- **Contracts** — интерфейсы в `lib/Contracts/`, биндинг контракт→реализация в `.settings.php`.
- **DTO** — наследуют `Beeralex\Core\Http\Resources\Resource`.

## Слои

```
Bitrix (обработчик доставки, события заказа)
        │
        ▼
Service-слой модуля  ← «богатое API» (CalculatorService, OrderService, PointService, …)
        │
        ▼
Sdk/ExtendedApiship (фасад)  →  расширенные Api-классы (lib/Sdk/Api/*)
        │                        + штатные классы vendor SDK
        ▼
ClientFactory → Apiship + GuzzleTokenAdapter (HTTP к api.apiship.ru / dev)
```

## Структура каталогов (целевая)

```
beeralex.apiship/
  include.php              # Loader::requireModule('beeralex.core') + functions.php
  .settings.php           # DI: Options, сервисы, контракты, controllers.defaultNamespace
  options.php / options_schema.php / default_option.php
  install/index.php version.php step.php unstep1.php unstep2.php
  lang/ru/...
  docs/                   # журнал памяти (этот каталог)
  lib/
    Options.php
    Client/ClientFactory.php
    Sdk/
      ExtendedApiship.php          # фасад: connections()/webhooks()/externalTracking()/...
      Api/{Connections,Webhooks,ExternalTracking,Lists,Orders}.php
      Entity/Request/*  Entity/Response/*
    Contracts/*Contract.php
    Service/{Calculator,Order,Point,Provider,Courier,Status,Webhook,Tracking}Service.php
    Dto/*Dto.php
    Builder/{ApiShipOrderRequestBuilder,CalculatorRequestBuilder}.php
    Delivery/
      ApiShipHandler.php           # extends Sale\Delivery\Services\Base (canHasProfiles)
      ApiShipProfile.php           # extends Base (isProfile), гибрид: CONFIG providerKey/tariffId
      Tracking/ApiShipTracking.php # extends Sale\Delivery\Tracking\Base
    Repository/{Order,StatusLog}Repository.php
    Model/{Order,StatusLog,WebhookLog}Table.php   # beeralex_apiship_*
    Enum/*.php
    StatusMapper.php               # ApiShip status -> Bitrix status
    EventHandlers.php              # OnSaleStatusOrderChange -> OrderService
    Controllers/WebhookController.php
    Exception/{ApiShipException,ApiShipValidationException}.php
```

## Ключевые решения (кратко, детали в DECISIONS.md)

1. **Профили доставки — гибрид.** Один класс `ApiShipProfile`; в CONFIG профиля хранятся
   `providerKey` и `tariffId`, а список доступных ТК/тарифов подтягивается из `lists/providers`.
2. **Момент отправки заказа в ApiShip — по настройке.** В настройках модуля выбирается статус
   заказа Bitrix, при переходе в который (`OnSaleStatusOrderChange`) заказ уходит в ApiShip.
3. **Идемпотентность вебхуков.** Все входящие вебхуки логируются в `beeralex_apiship_webhook_log`
   с дедупликацией по event/tracing id.
4. **Создание заказа — асинхронное** (`orders`, не `orders/sync`); финальные данные приходят вебхуком.

## Внешние ссылки

- Документация API: https://docs.apiship.ru
- OpenAPI-спека проекта: `openapi.yaml` в корне модуля
- SDK: `vendor/apiship/apiship-sdk-php` (namespace `Apiship\`)
