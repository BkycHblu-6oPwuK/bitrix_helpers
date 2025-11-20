<?php
declare(strict_types=1);

use Beeralex\Core\Config\Module\Schema\Schema;
use Beeralex\Core\Config\Module\Schema\SchemaTab;

return Schema::make()
    ->tab(
        'edit2',
        'JWT',
        'Токеновая авторизация (JWT)',
        function (SchemaTab $tab) {
            $tab->checkbox(
                'BEERALEX_USER_ENABLE_JWT_AUTH',
                'Включить авторизацию по JWT токенам',
                null,
                false,
                true
            );

            $tab->input(
                'BEERALEX_USER_JWT_SECRET_KEY',
                'Секретный ключ для JWT',
                null,
                null,
                false,
                '' // J3p6o4VwJkqU3yX7J2JQ0vS8Z0jvJmJjzqJjCj7y7v4jS2WlDgYj6Q==
            );

            $tab->input(
                'BEERALEX_USER_JWT_TTL',
                'Время жизни JWT токена (в секундах)',
                null,
                null,
                false,
                '3600'
            );

            $tab->input(
                'BEERALEX_USER_JWT_REFRESH_TTL',
                'Время жизни refresh токена (в секундах)',
                null,
                null,
                false,
                '2592000'
            );

            $tab->input(
                'BEERALEX_USER_JWT_ALGORITHM',
                'Алгоритм шифрования JWT',
                null,
                null,
                false,
                'HS256'
            );

            $tab->input(
                'BEERALEX_USER_JWT_ISSUER',
                'Издатель JWT токена (issuer)',
                null,
                null,
                false,
                'beeralex.user'
            );
        }
    );