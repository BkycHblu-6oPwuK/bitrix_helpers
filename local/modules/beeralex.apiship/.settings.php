<?php

use Beeralex\Apiship\Builder\ApiShipOrderRequestBuilder;
use Beeralex\Apiship\Client\ClientFactory;
use Beeralex\Apiship\Contracts\CalculatorServiceContract;
use Beeralex\Apiship\Contracts\CourierServiceContract;
use Beeralex\Apiship\Contracts\OrderServiceContract;
use Beeralex\Apiship\Contracts\PointServiceContract;
use Beeralex\Apiship\Contracts\ProviderServiceContract;
use Beeralex\Apiship\Contracts\StatusServiceContract;
use Beeralex\Apiship\Contracts\TrackingServiceContract;
use Beeralex\Apiship\Contracts\WebhookServiceContract;
use Beeralex\Apiship\Options;
use Beeralex\Apiship\Repository\OrderRepository;
use Beeralex\Apiship\Repository\StatusLogRepository;
use Beeralex\Apiship\Repository\WebhookLogRepository;
use Beeralex\Apiship\Service\CalculatorService;
use Beeralex\Apiship\Service\CourierService;
use Beeralex\Apiship\Service\OrderService;
use Beeralex\Apiship\Service\PointService;
use Beeralex\Apiship\Service\ProviderService;
use Beeralex\Apiship\Service\StatusService;
use Beeralex\Apiship\Service\TrackingService;
use Beeralex\Apiship\Service\WebhookService;
use Beeralex\Apiship\StatusMapper;

return [
    'services' => [
        'value' => [
            Options::class => [
                'className' => Options::class,
            ],
            ClientFactory::class => [
                'constructor' => static fn() => new ClientFactory(
                    service(Options::class)
                ),
            ],

            // Service-слой ("богатое API" модуля) — регистрируем как реализацию по контракту.
            CalculatorServiceContract::class => [
                'constructor' => static fn() => new CalculatorService(
                    service(ClientFactory::class),
                    service(Options::class)
                ),
            ],
            PointServiceContract::class => [
                'constructor' => static fn() => new PointService(
                    service(ClientFactory::class)
                ),
            ],
            ProviderServiceContract::class => [
                'constructor' => static fn() => new ProviderService(
                    service(ClientFactory::class)
                ),
            ],
            OrderServiceContract::class => [
                'constructor' => static fn() => new OrderService(
                    service(ClientFactory::class),
                    service(Options::class)
                ),
            ],
            CourierServiceContract::class => [
                'constructor' => static fn() => new CourierService(
                    service(ClientFactory::class)
                ),
            ],
            StatusServiceContract::class => [
                'constructor' => static fn() => new StatusService(
                    service(ClientFactory::class)
                ),
            ],
            WebhookServiceContract::class => [
                'constructor' => static fn() => new WebhookService(
                    service(ClientFactory::class)
                ),
            ],
            TrackingServiceContract::class => [
                'constructor' => static fn() => new TrackingService(
                    service(ClientFactory::class)
                ),
            ],

            // Конкретные реализации регистрируем и по собственному имени класса —
            // удобно, когда контракт избыточен (например, внутри самого модуля).
            CalculatorService::class => [
                'constructor' => static fn() => service(CalculatorServiceContract::class),
            ],
            PointService::class => [
                'constructor' => static fn() => service(PointServiceContract::class),
            ],
            ProviderService::class => [
                'constructor' => static fn() => service(ProviderServiceContract::class),
            ],
            OrderService::class => [
                'constructor' => static fn() => service(OrderServiceContract::class),
            ],
            CourierService::class => [
                'constructor' => static fn() => service(CourierServiceContract::class),
            ],
            StatusService::class => [
                'constructor' => static fn() => service(StatusServiceContract::class),
            ],
            WebhookService::class => [
                'constructor' => static fn() => service(WebhookServiceContract::class),
            ],
            TrackingService::class => [
                'constructor' => static fn() => service(TrackingServiceContract::class),
            ],

            ApiShipOrderRequestBuilder::class => [
                'constructor' => static fn() => new ApiShipOrderRequestBuilder(
                    service(Options::class)
                ),
            ],

            // Репозитории (Фаза 4) — доступ к beeralex_apiship_order/status_log/webhook_log.
            OrderRepository::class => [
                'className' => OrderRepository::class,
            ],
            StatusLogRepository::class => [
                'constructor' => static fn() => new StatusLogRepository(
                    service(OrderRepository::class)
                ),
            ],
            WebhookLogRepository::class => [
                'className' => WebhookLogRepository::class,
            ],
            StatusMapper::class => [
                'constructor' => static fn() => new StatusMapper(
                    service(StatusLogRepository::class)
                ),
            ],
        ],
        'readonly' => true,
    ],
    'controllers' => [
        'value' => [
            'defaultNamespace' => '\\Beeralex\\Apiship\\Controllers',
        ],
        'readonly' => true,
    ],
];
