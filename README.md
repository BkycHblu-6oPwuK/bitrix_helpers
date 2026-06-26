#
пример сайта на bitrix + nuxt

# инфоблоки

миграции есть в local/php_interface/migrations

инфоблоки под контент, видео, главную, баннер, вопросы и ответы. Базовая реализация.

под контент и главную есть компоненты в local/components/main|content

[restore.php](https://www.1c-bitrix.ru/download/files/scripts/restore.php)

[bitrixsetup.php](https://www.1c-bitrix.ru/download/files/scripts/bitrixsetup.php)


# fixes

1. Ошибки в проверке системы вида `В таблице b_messageservice_restriction поле ADDITIONAL_PARAMS "`ADDITIONAL_PARAMS` mediumtext NOT NULL" не соответствует описанию на диске "`ADDITIONAL_PARAMS` text NOT NULL"`

ошибки в - report.txt
запросы на исправление в - fix.sql

```bash
cat report.txt | sed -E '
s/В таблице ([^ ]+) поле ([^ ]+) "`[^`]+` ([^"]+)" не соответствует описанию на диске "`[^`]+` ([^"]+)"/ALTER TABLE \1 MODIFY COLUMN \2 \4;/g
' > fix.sql
```

2. При использовании ORM и xdebug может быть так что запрос зависает и создается большая нагрузка на диск.

Отключить xdebug либо закомментировать в /bitrix/modules/main/lib/db/result.php в методе getCount выброс исключения (throw new \Bitrix\Main\ObjectPropertyException("count"))