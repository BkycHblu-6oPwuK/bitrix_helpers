<?php

use Bitrix\Main\DI\ServiceLocator;
use Itb\Catalog\Location\Contracts\BitrixLocationResolverInterface;
use Itb\Catalog\Location\Services\DadataService;
use Itb\Notification\Contracts\SmsCodeContract;
use Itb\Notification\Contracts\SmsContract;
use Itb\Notification\Services\Sms\SmsAeroService;
use Itb\Notification\Services\Sms\SmsCodeService;

ServiceLocator::getInstance()->addInstanceLazy(SmsContract::class, [
    'className' => SmsAeroService::class
]);
ServiceLocator::getInstance()->addInstanceLazy(SmsCodeContract::class, [
    'className' => SmsCodeService::class,
    'constructorParams' => static function () {
        return [ServiceLocator::getInstance()->get(SmsContract::class)];
    },
]);

ServiceLocator::getInstance()->addInstanceLazy(BitrixLocationResolverInterface::class, [
    'className' => DadataService::class
]);