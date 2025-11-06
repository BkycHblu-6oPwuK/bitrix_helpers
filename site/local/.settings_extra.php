<?php

/**
 * зарегистрированные зависимости в этом файле перебивают зарегистрированные зависимости в модулях
 */
return [
    'composer' => [
        'value' => ['config_path' => 'composer.json']
    ],
    'routing' => ['value' => [
        'config' => ['web.php', 'api.php']
    ]],
    'beeralex.oauth2' => [
        'value' => [
            'private_key' => '/var/www/local/config/private.key',
            'public_key' => '/var/www/local/config/public.key',
            'private_key_passphrase' => null,
            'encryption_key' => 'KMC0N/+dFrGoB2CYlH3q2XQwLJBLvY2En6+fS4i9rZs=', // ключ шифрования
        ]
    ],
    // 'beeralex.api' => [
    //     'value' => [
    //         'remove_parts' => [
    //             '/api/',
    //             '/v1/',
    //         ]
    //     ]
    // ],
];
