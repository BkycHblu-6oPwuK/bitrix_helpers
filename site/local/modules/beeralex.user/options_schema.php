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
                ''
            );

            $tab->input(
                'BEERALEX_USER_JWT_TTL',
                'Время жизни JWT токена (в секундах)',
                null,
                null,
                false,
                '1200' // 20 минут, этого достаточно для большинства случаев. Если нужно больше - всегда можно обновить токен через refresh токен
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