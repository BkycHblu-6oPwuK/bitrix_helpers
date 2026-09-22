# Покрытие API ApiShip в SDK и пробелы

SDK: `vendor/apiship/apiship-sdk-php` (namespace `Apiship\`, PSR-4 → `src/`).
Транспорт — Guzzle 7. Авторизация — заголовок `Authorization: <token>` (без `Bearer`).
Base URL: prod `https://api.apiship.ru/v1/`, dev `http://api.dev.apiship.ru/v1/`.

Расширение делаем **внутри модуля** в `lib/Sdk/` (наследуем `Apiship\Api\AbstractApi`,
`Apiship\Entity\AbstractRequest`, `Apiship\Entity\AbstractResponse`), vendor не патчим.

## ✅ Уже покрыто SDK

| Группа       | Методы SDK |
|--------------|-----------|
| Orders       | `create`, `createSync`, `update`, `delete`, `cancel`, `getOrderInfo`, `resend` |
| Statuses     | `getStatusByOrderId`, `getStatusByClientNumber`, `getStatuses`, `getStatusesByDate`, `getStatusHistory`, `getStatusHistoryByDate` |
| Waybills     | `getWaybills` |
| Calculator   | `calculate` |
| Lists        | `getProviders`, `getPoints`, `getPointTypes`, `getServices` |
| CourierCall  | `create`, `cancel` |
| Login        | неявно внутри адаптера (`GuzzleAdapter::login()` / `GuzzleTokenAdapter`) |

## ❌ Не покрыто — требует расширения (lib/Sdk/Api/*)

| Приоритет | Группа / метод | OpenAPI | Статус в модуле |
|-----------|----------------|---------|------------------|
| P1 | `webhooks` — POST/GET/DELETE подписки | `/webhooks`, `/webhooks/{uuid}` | планируется (Фаза 4) |
| P1 | `lists/statuses` — справочник статусов | `/lists/statuses` | планируется (Фаза 1/2) |
| P2 | `connections` CRUD — подключения к ТК | `/connections*` | планируется (Фаза 1) |
| P2 | `lists/tariffs` — актуальные тарифы | `/lists/tariffs` | планируется (Фаза 1) |
| P2 | `calculator/intervals` — интервалы доставки | `/calculator/intervals` | планируется (Фаза 1) |
| P3 | `externalTracking` — трекинг сторонних заказов | `/externalTracking/orders*` | задел |
| P3 | Orders: список с фильтром/пагинацией (`GET /orders`) | — | задел |
| P3 | `orders/labels` — печать этикеток | `/orders/labels` | вне scope (UI) |
| P3 | `orders/{id}/courier`, `/code`, `/recipientcode` | — | задел |

## Явно вне scope (сейчас не делаем)

- Любой фронт/Nuxt, REST под фронт (кроме входящего webhook).
- UI печати этикеток/актов.
- Возвраты (`/orders/return`) — только заметка на будущее.
- Биллинг/чеки apiPay (`/pay/receipt*`), баланс.
- Провайдер-специфичные методы (dpd, x5, boxberry, yataxi, logsis, ozondel, cse).

## Рецепт расширения SDK внутри модуля

1. Новый Api-класс `lib/Sdk/Api/Xxx.php extends Apiship\Api\AbstractApi` — методы дергают
   `$this->adapter->get|post|put|delete(...)`, декодируют JSON, гидратируют Response.
2. Зарегистрировать его в фасаде `lib/Sdk/ExtendedApiship.php`.
3. Request: `lib/Sdk/Entity/Request/XxxRequest.php extends Apiship\Entity\AbstractRequest`
   (свойства + fluent-сеттеры; `asJson()` бесплатно через trait).
4. Response: `lib/Sdk/Entity/Response/XxxResponse.php extends Apiship\Entity\AbstractResponse`
   (свойства + геттеры/сеттеры; неизвестные поля из JSON молча игнорируются magic `__set`).

### Нюансы
- Заголовок авторизации ставит middleware адаптера — руками не добавлять.
- Обязательные поля Request реализуются геттером, бросающим `RequiredParameterException`.
- Base URL при необходимости меняется `adapter->setCustomUrl()`.
