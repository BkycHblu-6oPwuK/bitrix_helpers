<?

use Beeralex\Api\ApiResult;
use Beeralex\Api\Domain\Iblock\Content\ContentRepository;
use Beeralex\Api\Domain\Iblock\Content\ContentService;
use Beeralex\Api\Domain\User\UserService;
use Beeralex\Api\Options;
use Beeralex\Catalog\Enum\DIServiceKey;
use Bitrix\Main\Loader;

return [
    'services' => [
        'value' => [
            Options::class => [
                'className' => Options::class,
            ],
            ApiResult::class => [
                'className' => ApiResult::class,
            ],
            ContentRepository::class => [
                'constructor' => static function() {
                    return new ContentRepository('content');
                },
            ],
            ContentService::class => [
                'constructor' => static function() {
                    Loader::requireModule('beeralex.catalog');
                    return new ContentService(service(ContentRepository::class), service(DIServiceKey::PRODUCT_REPOSITORY->value));
                },
            ],
            UserService::class => [
                'className' => UserService::class,
            ],
        ]
    ]
];
