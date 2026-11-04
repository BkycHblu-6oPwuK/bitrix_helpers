<?php

use Beeralex\Apiship\Support\OrderRequestBuilder;
use Beeralex\Apiship\Support\CalculatorRequestBuilder;
use Beeralex\Apiship\Support\ClientFactory;
use Beeralex\Apiship\Contracts\CalculatorServiceContract;
use Beeralex\Apiship\Contracts\ConnectionServiceContract;
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
use Beeralex\Apiship\Service\ConnectionService;
use Beeralex\Apiship\Service\CourierService;
use Beeralex\Apiship\Service\OrderService;
use Beeralex\Apiship\Service\PointService;
use Beeralex\Apiship\Service\ProfileCalculator;
use Beeralex\Apiship\Service\ProviderService;
use Beeralex\Apiship\Service\StatusService;
use Beeralex\Apiship\Service\TrackingService;
use Beeralex\Apiship\Service\WebhookService;
use Beeralex\Apiship\StatusMapper;
use Beeralex\Apiship\Support\CalculatedTariffStore;
use Beeralex\Apiship\Support\CalculationContextFactory;
use Beeralex\Apiship\Support\OrderAddressReader;
use Beeralex\Apiship\Support\RecipientReader;
use Beeralex\Apiship\Support\OrderItemsMapper;
use Beeralex\Apiship\Support\ProfileFormOptions;
use Beeralex\Apiship\Support\TariffSelector;

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
            CalculatorServiceContract::class => [
                'constructor' => static fn() => new CalculatorService(
                    service(ClientFactory::class),
                    service(Options::class),
                    service(CalculatorRequestBuilder::class)
                ),
            ],
            ConnectionServiceContract::class => [
                'constructor' => static fn() => new ConnectionService(
                    service(ClientFactory::class),
                    service(Options::class)
                ),
            ],
            PointServiceContract::class => [
                'constructor' => static fn() => new PointService(
                    service(ClientFactory::class),
                    service(Options::class)
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
                    service(Options::class),
                    service(OrderRepository::class),
                    service(OrderRequestBuilder::class),
                    service(OrderItemsMapper::class),
                    service(RecipientReader::class),
                    service(CalculatedTariffStore::class)
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
                    service(ClientFactory::class),
                    service(WebhookLogRepository::class),
                    service(OrderRepository::class),
                    service(StatusLogRepository::class),
                    service(StatusMapper::class),
                ),
            ],
            TrackingServiceContract::class => [
                'constructor' => static fn() => new TrackingService(
                    service(ClientFactory::class)
                ),
            ],
            CalculatorService::class => [
                'constructor' => static fn() => service(CalculatorServiceContract::class),
            ],
            ConnectionService::class => [
                'constructor' => static fn() => service(ConnectionServiceContract::class),
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
            OrderRequestBuilder::class => [
                'className' => OrderRequestBuilder::class,
            ],
            CalculatorRequestBuilder::class => [
                'className' => CalculatorRequestBuilder::class,
            ],

            OrderRepository::class => [
                'className' => OrderRepository::class,
            ],
            WebhookLogRepository::class => [
                'className' => WebhookLogRepository::class,
            ],
            StatusLogRepository::class => [
                'className' => StatusLogRepository::class,
            ],
            StatusMapper::class => [
                'constructor' => static fn() => new StatusMapper(
                    service(Options::class)
                ),
            ],
            TariffSelector::class => [
                'className' => TariffSelector::class
            ],
            OrderAddressReader::class => [
                'constructor' => static fn() => new OrderAddressReader(
                    service(Options::class)
                ),
            ],
            RecipientReader::class => [
                'constructor' => static fn() => new RecipientReader(
                    service(Options::class),
                    service(OrderAddressReader::class)
                ),
            ],
            OrderItemsMapper::class => [
                'constructor' => static fn() => new OrderItemsMapper(
                    service(Options::class)
                ),
            ],
            CalculatedTariffStore::class => [
                'constructor' => static fn() => new CalculatedTariffStore(
                    service(Options::class)
                ),
            ],
            CalculationContextFactory::class => [
                'constructor' => static fn() => new CalculationContextFactory(
                    service(Options::class),
                    service(OrderAddressReader::class)
                ),
            ],
            ProfileCalculator::class => [
                'constructor' => static fn() => new ProfileCalculator(
                    service(CalculatorServiceContract::class),
                    service(CalculationContextFactory::class),
                    service(TariffSelector::class)
                ),
            ],
            ProfileFormOptions::class => [
                'constructor' => static fn() => new ProfileFormOptions(
                    service(ConnectionServiceContract::class),
                    service(ProviderServiceContract::class)
                ),
            ]
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
