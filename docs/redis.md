В bitrix vm модуль redis для php уже установлено
Проверить установлен ли модуль можно в phpinfo, а установлено ли расширение с помощью команды проверки статуса redis в консоли
Проверить статус - sudo systemctl status redis, если redis установлен, но выключен то там будет написано что то вроде такого:
redis.service - Advanced key-value store 
Loaded: loaded (/lib/systemd/system/redis.service; enabled; vendor preset: enabled) 
Active: inactive (dead)
	или если активен то - Active: active
    
В bitrix vm файл конфигурации должен располагаться по пути - /etc/redis/redis.conf
Рекомендую в нем установить лимит потребляемой памяти, redis по умолчанию потребляет всю доступную оперативную память
Нужно найти закомментированный параметр – ‘# maxmemory <bytes>’ и раскоментировать и установить значение в байтах
Или в гигабайтах - maxmemory 2gb
Так же можно настроить политику вытеснения данных - maxmemory-policy allkeys-lru, allkeys-lru - удаляет старые ключи с наименьшей частотой использования.
После внесения изменений выполните команду - sudo systemctl restart redis
