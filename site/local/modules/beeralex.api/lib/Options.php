<?php
declare(strict_types=1);

namespace Beeralex\Api;

use Beeralex\Core\Config\AbstractOptions;

final class Options extends AbstractOptions
{
    protected function mapOptions(array $options): void
    {
        
    }

    public function getModuleId(): string
    {
        return 'beeralex.catalog';
    }
}