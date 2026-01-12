<?

use Beeralex\Api\ApiResult;
use Beeralex\Api\Domain\Form\FormRepository;
use Beeralex\Api\Domain\Form\FormService;
use Beeralex\Api\Domain\Iblock\Content\ContentRepository;
use Beeralex\Api\Domain\Iblock\Content\MainRepository;
use Beeralex\Api\Domain\Iblock\Content\MainService;
use Beeralex\Api\Domain\User\UserService;
use Beeralex\Api\Options;
use Beeralex\Catalog\Enum\DIServiceKey;
use Beeralex\Core\Service\FileService;
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
            MainRepository::class => [
                'constructor' => static function () {
                    return new MainRepository('main');
                },
            ],
            FormRepository::class => [
                'className' => FormRepository::class,
            ],
            ContentRepository::class => [
                'constructor' => static function () {
                    return new ContentRepository('content', service(FileService::class));
                },
            ],
            MainService::class => [
                'constructor' => static function () {
                    Loader::requireModule('beeralex.catalog');
                    return new MainService(service(MainRepository::class), service(DIServiceKey::PRODUCT_REPOSITORY->value));
                },
            ],
            UserService::class => [
                'className' => UserService::class,
            ],
            FormService::class => [
                'constructor' => static function () {
                    return new FormService(service(FormRepository::class));
                },
            ],
        ]
    ]
];
