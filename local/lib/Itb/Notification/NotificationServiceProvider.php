<?php

namespace Itb\Notification;

use Itb\Notification\Contracts\SmsCodeContract;
use Itb\Notification\Contracts\SmsContract;
use Itb\Notification\Services\Sms\SmsAeroService;
use Itb\Notification\Services\Sms\SmsCodeService;

class NotificationServiceProvider extends \Itb\Core\DI\AbstractServiceProvider
{
    protected function registerServices(): void
    {
        $this->bind(SmsContract::class, SmsAeroService::class);
        $this->bind(SmsCodeContract::class, SmsCodeService::class, fn() => [$this->locator->get(SmsContract::class)]);
    }
}