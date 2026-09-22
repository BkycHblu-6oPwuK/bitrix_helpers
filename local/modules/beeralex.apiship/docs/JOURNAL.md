# Журнал разработки

Хронология работ по модулю. Новые записи — сверху.

---

## 2026-09-19 — Фаза 4: ORM, репозитории, события, вебхук, StatusMapper

- `lib/Model/{Order,StatusLog,WebhookLog}Table` — DataManager + TableManagerTrait,
  префикс `beeralex_apiship_`. UNIQUE-индекс на `webhook_log.EVENT_ID` создаётся в
  `InstallDB()` через `queryExecute` (MySQL не поддерживает `IF NOT EXISTS` до 8.0 —
  используем `try/catch` при повторной установке).
- `lib/Repository/{Order,StatusLog,WebhookLog}Repository` — прямые ORM-методы (без
  AbstractRepository: специфичные методы `getBitrixOrderId`, `logIfChanged`,
  `registerEvent` для каждой таблицы). Дедупликация вебхуков — через `isTableExists` +
  попытка вставки с перехватом дублей (`registerEvent` возвращает false если уже есть).
- `lib/StatusMapper` — маппинг `apiShipStatusKey → Bitrix STATUS_ID` (константный массив,
  переопределяется через `getMap()`). `applyToBitrixOrder()` загружает `Order`, меняет
  `STATUS_ID`, вызывает `save()`. Также пишет в `StatusLogRepository`.
- `lib/EventHandlers::onSaleStatusOrderChange` — реагирует на `EVENT_ENTITY` (объект Order),
  проверяет `VALUE === Options::sendOnStatusId`, вызывает
  `OrderServiceContract::createForBitrixOrder($order->getId())`.
- `lib/Controllers/WebhookController` — принимает POST от ApiShip на
  `/bitrix/api/v1/apiship/webhook/{secret}`. Валидирует `{secret}` по `.env APISHIP_WEBHOOK_SECRET`.
  Парсит JSON-тело, создаёт `WebhookEventDto`, делегирует в `WebhookServiceContract::handle()`.
- `site/local/routes/api.php` — добавлен маршрут `apiship/webhook/{secret}`.
- `install/index.php` — `InstallDB()`/`UnInstallDB()` создают/удаляют три таблицы;
  `InstallEvents()`/`UnInstallEvents()` регистрируют/отписывают `OnSaleStatusOrderChange` и
  `onSaleDeliveryHandlersClassNamesBuildList`. Классы адресуются строковыми FQCN-константами
  (guard на ранних фазах снят — все классы теперь существуют).
- `.settings.php` — добавлены `OrderRepository`, `StatusLogRepository`,
  `WebhookLogRepository`, `StatusMapper`.

### Итог: все фазы 0–4 реализованы. Модуль готов к установке и тестированию.

---

## 2026-09-19 — Фаза 3: обработчик доставки, профиль, трекинг

- `lib/Delivery/ApiShipHandler extends Sale\Delivery\Services\Base` — `handlerCode =
  BEERALEX_APISHIP`, `canHasProfiles = true`. Регистрация в системе — статический метод
  `onSaleDeliveryHandlersClassNamesBuildList()`, возвращающий `EventResult::SUCCESS` с
  `[self::class => __FILE__]` (обработчик события `sale::onSaleDeliveryHandlersClassNamesBuildList`,
  см. `bitrix/modules/sale/lib/delivery/services/manager.php`). `getConfigStructure()` даёт
  минимальный `CONFIG.MAIN` (`PROVIDER_KEY`/`TARIFF_ID`/`PROVIDER_CONNECT_ID`, все опциональны).
  `calculateConcrete()` вызывает `CalculatorServiceContract`, выбирает тариф по CONFIG или
  самый дешёвый (`pickTariff()`), если CONFIG пуст — расчёт идёт по всем ТК ApiShip.
- `lib/Delivery/ApiShipProfile extends ApiShipHandler` — гибридная схема профилей (ADR-004):
  один класс, `CONFIG.MAIN.PROVIDER_KEY/TARIFF_ID` задают конкретную ТК/тариф; список ТК для
  заполнения этих полей берётся из `ProviderServiceContract::getProviders()`.
- `lib/Delivery/Tracking/ApiShipTracking extends Sale\Delivery\Tracking\Base` — `getStatus()`
  трактует `$trackingNumber` как `apiShipOrderId` (агрегатор сам транслирует статус ТК).
- `lang/ru/lib/Delivery/*` — локализация заголовков/описаний классов.

### Заметка
- `StatusResult`/`Statuses` (namespace `Bitrix\Sale\Delivery\Tracking`) определены в
  `lib/delivery/tracking/manager.php`, а не в отдельном `statusresult.php` — важно при поиске.

---

## 2026-09-19 — Фаза 2: Contracts + DTO + Service-слой + Builder

- `lib/Dto/*` — 11 DTO (`extends Beeralex\Core\Http\Resources\Resource`): `TariffDto`,
  `PointDto`, `AddressDto`, `PlaceDto`, `RecipientDto`, `ItemDto`, `CalculationRequestDto`,
  `CreateOrderDto`, `OrderResultDto`, `StatusDto`, `WebhookEventDto`.
- `lib/Contracts/*ServiceContract` — 8 интерфейсов сервисов (Calculator/Point/Provider/
  Order/Courier/Status/Webhook/Tracking).
- `lib/Exception/{ApiShipException,ApiShipValidationException}`.
- `lib/Service/*Service` — реализации контрактов поверх `ExtendedApiship`/штатного SDK.
  `Service/Support/RequestHydrator` — трейт `hydrate()`, заполняющий Request/Part-объекты
  SDK через `setXxx`-сеттеры (единый подход и для public-свойств, и для protected+setter).
- `lib/Builder/ApiShipOrderRequestBuilder` — собирает `CreateOrderDto` из `Bitrix\Sale\Order`
  (находит доставку ApiShip в `ShipmentCollection`, читает свойства заказа по коду,
  товары — из `ShipmentItemCollection`/`BasketItem`).
- `.settings.php` — зарегистрированы все сервисы (по контракту и по имени класса) и builder.

### Важные технические заметки
- `Bitrix\Main\Type\Dictionary::get($name)` **не принимает** второй аргумент (default) —
  в DTO использован `?? []` вместо `get('key', [])`.
- `StatusService`/`WebhookService`/`Builder` ссылаются на классы Фазы 3/4
  (`ApiShipHandler`, `ApiShipProfile`, `StatusMapper`, репозитории) через строковые FQCN +
  `class_exists()`/`is_a()` — обеспечивает поэтапную установку модуля без ошибок.

---

## 2026-09-19 — Фаза 1: клиент + расширение SDK

- `lib/Client/TokenAdapter` — наследник `GuzzleTokenAdapter` с поддержкой `verify` (SSL) и
  `customUrl`; повторяет штатные middleware (подстановка токена + `handleResponse`).
- `lib/Client/ClientFactory` — собирает `ExtendedApiship` из настроек (`Options`), кэширует
  экземпляр; зарегистрирован в `.settings.php` через `constructor`.
- `lib/Sdk/ExtendedApiship` — фасад над SDK: `connections()`, `webhooks()`,
  `externalTracking()`, `listsExtended()`, `ordersExtended()`.
- Расширение SDK (`lib/Sdk/Api/`): `AbstractExtendedApi` (requestGet/Post/Put/Delete →
  `RawResponse`), `Webhooks`, `Connections`, `Lists` (statuses/providerStatuses/tariffs/
  deliveryTypes/pickupTypes), `Orders` (list/statusHistoryByInterval/labels), `ExternalTracking`.
- `lib/Sdk/Entity/Response/RawResponse` — универсальный ответ (произвольные поля JSON → `data[]`).

### Заметка
- Линтер ложно помечает `Lists`/`Orders` в `ExtendedApiship` как undefined из-за совпадения
  короткого имени с vendor `Apiship\Api\Lists`/`Orders`. Файлы валидны (namespace
  `Beeralex\Apiship\Sdk\Api`). Исчезает после переиндексации.

---

## 2026-09-19 — Фаза 0: каркас модуля

- Создан журнал памяти: `ARCHITECTURE.md`, `SDK_GAPS.md`, `DECISIONS.md`, `JOURNAL.md`.
- Изучены базовые классы `beeralex.core`: `AbstractOptions`, `Service\Api\ApiService`,
  `Service\Api\ClientService`, `Http\Resources\Resource`, `Traits\TableManagerTrait`.
- Изучен SDK ApiShip: покрытые/непокрытые методы зафиксированы в `SDK_GAPS.md`.
- Утверждены решения ADR-001…007 (`DECISIONS.md`).

### План фаз
- **Фаза 0** — каркас: docs, install/index.php, Options, .settings.php, options.php. ← текущая
- **Фаза 1** — ClientFactory + ExtendedApiship + расширение SDK.
- **Фаза 2** — Contracts + DTO + Service-слой + Builder.
- **Фаза 3** — ApiShipHandler + Profile + Tracking.
- **Фаза 4** — ORM + EventHandlers + Webhook-контроллер + StatusMapper.

### TODO / открытые вопросы
- Уточнить набор статусов Bitrix для маппинга (StatusMapper) на реальном каталоге статусов.
- Проверить, включён ли `apiship/apiship-sdk-php` в корневой autoload проекта (composer require выполнен).
