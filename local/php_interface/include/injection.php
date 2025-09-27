<?php

use Bitrix\Main\DI\ServiceLocator;
use Itb\Catalog\Location\BitrixLocationResolver;
use Itb\Catalog\Location\Contracts\BitrixLocationResolverInterface;
use Itb\Catalog\Location\Contracts\LocationApiClientInterface;
use Itb\Catalog\Location\Services\DadataService;
use Itb\Notification\Contracts\SmsCodeContract;
use Itb\Notification\Contracts\SmsContract;
use Itb\Notification\Services\Sms\SmsAeroService;
use Itb\Notification\Services\Sms\SmsCodeService;

$serviceLocator = ServiceLocator::getInstance();

$serviceLocator->addInstanceLazy(SmsContract::class, [
    'className' => SmsAeroService::class
]);
$serviceLocator->addInstanceLazy(SmsCodeContract::class, [
    'className' => SmsCodeService::class,
    'constructorParams' => static function () use ($serviceLocator) {
        return [$serviceLocator->get(SmsContract::class)];
    },
]);

$serviceLocator->addInstanceLazy(LocationApiClientInterface::class, [
    'className' => DadataService::class
]);
$serviceLocator->addInstanceLazy(BitrixLocationResolverInterface::class, [
    'className' => BitrixLocationResolver::class,
    'constructorParams' => static function () use ($serviceLocator) {
        return [
            $serviceLocator->get(LocationApiClientInterface::class),
            $serviceLocator->get(\Itb\Core\Logger\LoggerFactoryInterface::class)->channel('location'),
        ];
    },
]);
