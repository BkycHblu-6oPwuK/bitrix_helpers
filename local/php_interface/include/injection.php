<?php

use Bitrix\Main\DI\ServiceLocator;
use Itb\Contracts\SmsCodeContract;
use Itb\Contracts\SmsContract;
use Itb\Services\Sms\SmsAeroService;
use Itb\Services\Sms\SmsCodeService;

ServiceLocator::getInstance()->addInstanceLazy(SmsContract::class, [
    'className' => SmsAeroService::class
]);
ServiceLocator::getInstance()->addInstanceLazy(SmsCodeContract::class, [
    'className' => SmsCodeService::class,
    'constructorParams' => static function () {
        return [ServiceLocator::getInstance()->get(SmsContract::class)];
    },
]);