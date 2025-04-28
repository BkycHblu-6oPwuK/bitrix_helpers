# Общее

- Удалить модули vote, Push And Pull
- удалить /bitrix/redirect.php

# Аналитика битрикс
выключить можно в settings.php

```php
  'analytics_counter'  => array(
    'value' => array(
      'enabled' => false,
    ),
  ),
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
define("CACHED_b_event", 3600);
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