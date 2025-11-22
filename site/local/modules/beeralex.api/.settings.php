<?

use Beeralex\Api\ApiResult;
use Beeralex\Api\UrlService;

return [
    'remove_parts' => [
        'value' => [
            'api',
            'v1',
        ]
    ],
    'services' => [
        'value' => [
            UrlService::class => [
                'className' => UrlService::class,
            ],
            ApiResult::class => [
                'className' => ApiResult::class,
            ],
        ]
    ]
];
