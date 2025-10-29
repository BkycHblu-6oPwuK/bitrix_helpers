<?php

namespace Beeralex\Notification;

use Beeralex\Core\Config\AbstractOptions;

final class Options extends AbstractOptions
{
    public readonly bool $moduleEnable;

    protected function mapOptions(array $options): void
    {
        $this->moduleEnable = $options['MODULE_ENABLED'] ?? false;
    }

    public function getModuleId(): string
    {
        return 'beeralex.notification';
    }
}
