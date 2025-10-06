<?php

namespace App\Notification;

use App\Notification\Contracts\SmsCodeContract;
use App\Notification\Contracts\SmsContract;
use App\Notification\Services\Sms\SmsAeroService;
use App\Notification\Services\Sms\SmsCodeService;

class NotificationServiceProvider extends \Beeralex\Core\DI\AbstractServiceProvider
{
    protected function registerServices(): void
    {
        $this->bind(SmsContract::class, SmsAeroService::class);
        $this->bind(SmsCodeContract::class, SmsCodeService::class, fn() => [$this->locator->get(SmsContract::class)]);
    }
}