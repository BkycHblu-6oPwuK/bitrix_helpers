# Nuxt Cache Invalidate API

Это API для инвалидирования кэша в приложении Nuxt.

## Использование

Отправьте POST-запрос на `/api/__cache__/invalidate` с телом:

```json
{
  "type": "all"
}
```

`type` может быть:
- `all` — инвалидирует весь кэш
- `catalog` — инвалидирует кэш каталога
- `articles` — инвалидирует кэш статей
- `other` — инвалидирует прочий кэш

При добавлении нового кешированного маршрута необходимо обновить соответствующую группу в API.

Далее в битрикс:

1. События регистрируются - `/local/modules/beeralex.api/include/events.php`
2. В обработчиках событий добавляется вызов `\Beeralex\Api\Domain\Services\CacheInvalidateService` для соответствующих типов кэша.