<?

use Beeralex\Api\ApiResult;
use Beeralex\Api\Domain\Form\FormAnswerRepository;
use Beeralex\Api\Domain\Form\FormHandlers;
use Beeralex\Api\Domain\Form\FormRepository;
use Beeralex\Api\Domain\Form\FormService;
use Beeralex\Api\Domain\Iblock\Content\MainRepository;
use Beeralex\Api\Domain\User\UserService;
use Beeralex\Api\Options;

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
            FormAnswerRepository::class => [
                'className' => FormAnswerRepository::class,
            ],
            UserService::class => [
                'className' => UserService::class,
            ],
            FormService::class => [
                'constructor' => static function() {
                    return new FormService(service(FormRepository::class), service(FormAnswerRepository::class));
                },
            ],
            FormHandlers::class => [
                'constructor' => static function() {
                    return new FormHandlers(service(FormService::class));
                },
            ],
        ]
    ]
];
