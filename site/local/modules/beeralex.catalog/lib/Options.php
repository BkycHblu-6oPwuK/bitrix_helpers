<?php
declare(strict_types=1);

namespace Beeralex\Catalog;

use Beeralex\Core\Config\AbstractOptions;

final class Options extends AbstractOptions
{
    public readonly string $apiKey;
    public readonly string $secretKey;

    protected function mapOptions(array $options): void
    {
        $this->apiKey = (string)($options['API_KEY'] ?? '');
        $this->secretKey = (string)($options['SECRET_KEY'] ?? '');
    }

    public function getModuleId(): string
    {
        return 'beeralex.catalog';
    }
}