<?php
declare(strict_types=1);

namespace Beeralex\Api;

use Beeralex\Core\Config\AbstractOptions;

final class Options extends AbstractOptions
{
    public readonly bool $spaApiEnabled;

    protected function mapOptions(array $options): void
    {
        $this->spaApiEnabled = (bool)($options['spa_api_enabled'] ?? false);
    }

    public function getModuleId(): string
    {
        return 'beeralex.api';
    }
}