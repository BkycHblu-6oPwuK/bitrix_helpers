# Общее

- Удалить модули vote, Push And Pull
- удалить /bitrix/redirect.php

установить
- itb.core
- itb.favorite
- itb.reviews
- по желанию webprostor.smtp (функция уже есть в init.php)

под vite стоит использовать актуальную сборку из репозитория - example_template

иб статьи:
- сим.код - articles
- сим.код api - ArticlesApi

иб каталог:
- сим.код - catalog
- сим.код api - CatalogApi

иб предложений:
- сим.код - offers
- сим.код api - OffersApi

свойства каталога:
- PRICE_BY_SORT - число, цена для сортировки
- MIN_PRICE - число, минимальная цена

доп поля разделов каталога:
- UF_SHOW_MENU - Да/Нет, Выводить в меню
- UF_MENU_COLUMN - список (one, two, three - xml id), В какой колонке меню выводить
- UF_CUSTOM_NAME - строка, Кастомное название
- UF_MENU_ROOT - Да/Нет, Корневой раздел

Так же в корне миграции для создания иб и хайлоада
- инфоблок вопросы и ответы
- разделы каталога на главной
- контент
- баннер на главной
- хайлоад смс-билдинг

# settings битрикс

```php
  'analytics_counter'  => array(
    'value' => array(
      'enabled' => false,
    ),
  ),
  'composer' => [
    'value' => ['config_path' => 'composer.json']
  ],
  'exception_handling' =>
  array(
    'value' =>
    array(
      'debug' => true,
      'handled_errors_types' => 4437,
      'exception_errors_types' => 4437,
      'ignore_silence' => false,
      'assertion_throws_exception' => true,
      'assertion_error_type' => 256,
      'log' =>
      array(
        'settings' =>
        array(
          'file' => 'local/logs/error.log',
          'log_size' => 1000000,
        ),
      ),
    ),
    'readonly' => false,
  ),
  // 'cache' => 
  // array (
  //   'value' => 
  //   array (
  //     'type' => 'redis',
  //     'redis' => 
  //     array (
  //       'host' => 'redis',
  //       'port' => '6379',
  //     ),
  //     'sid' => $_SERVER["DOCUMENT_ROOT"] . '#01',
  //   ),
  // ),
  // 'cache' => [
  //   'value' => [
  //     'type' => 'memcache',
  //     'memcache' => [
  //       'host' => 'memcached',
  //       'port' => 11211,
  //     ],
  //     'sid' => $_SERVER["DOCUMENT_ROOT"] . "#01"
  //   ],
  // ],
  // 'session' =>
  // array(
  //   'value' =>
  //   array(
  //     'mode' => 'default',
  //     'handlers' =>
  //     array(
  //       'general' =>
  //       array(
  //         'type' => 'redis',
  //         'port' => '6379',
  //         'host' => 'redis',
  //       ),
  //     ),
  //   ),
  // ),
```

## after connect
Что то из этого можно удалить, но в целом
```php
$this->queryExecute("SET NAMES 'utf8mb4'");
$this->queryExecute('SET collation_connection = "utf8mb4_0900_ai_ci"');
$this->queryExecute("SET sql_mode=''");
$this->queryExecute("SET wait_timeout=68800");
```

## dbconn

```php
define("BX_USE_MYSQLI", true);
define("DBPersistent", false);
$DBDebug = false;
$DBDebugToFile = false;

@set_time_limit(0);
define("MYSQL_TABLE_TYPE", "INNODB");
define("DELAY_DB_CONNECT", true);
define("CACHED_b_file", 3600);
define("CACHED_b_file_bucket_size", 10);
define("CACHED_b_lang", 3600);
define("CACHED_b_option", 3600);
define("CACHED_b_lang_domain", 3600);
define("CACHED_b_site_template", 3600);
define("CACHED_b_event", 3600); // или если крон define("CACHED_b_event", false);
define("CACHED_b_agent", 3660);
define("CACHED_menu", 3600);
define("BX_UTF", true);
define("BX_FILE_PERMISSIONS", 0644);
define("BX_DIR_PERMISSIONS", 0755);
@umask(~(BX_FILE_PERMISSIONS | BX_DIR_PERMISSIONS) & 0777);
define("BX_DISABLE_INDEX_PAGE", true);

if(!(defined("CHK_EVENT") && CHK_EVENT===true))
   define("BX_CRONTAB_SUPPORT", true);

define("CACHED_b_search_tags", true);
define("NO_AGENT_STATISTIC", true);
```

# cron
можно просто модуль установить - агенты на кроне и перевести на крон.

``` */1 * * * * /usr/bin/php -f /home/bitrix/www/bitrix/php_interface/cron_events.php ```

```php
php_sapi_name() == "cli"
```