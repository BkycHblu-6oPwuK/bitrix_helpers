<?php

namespace Beeralex\Api\Domain\Services;

use Beeralex\Core\Config\Config;
use Bitrix\Main\Web\HttpClient;

class CacheInvalidateService
{
    public function getApiBase(): string
    {
        return service(Config::class)['NUXT_API_BASE'];
    }

    public function getApiUrl(): string
    {
        return $this->getApiBase() . '/api/__cache__/invalidate';
    }

    public function getClient(): HttpClient
    {
        return new HttpClient([
            'socketTimeout' => 2,
            'streamTimeout' => 2,
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . service(Config::class)['NUXT_SECRET_TOKEN'],
            ]
        ]);
    }

    public function invalidateCache(string $type = 'all'): void
    {
        try {
            $client = $this->getClient();

            $client->post(
                $this->getApiUrl(),
                json_encode([
                    'type' => $type,
                    'timestamp' => time(),
                ], JSON_THROW_ON_ERROR),
            );
        } catch (\Throwable $e) {
            \Beeralex\Api\log($e->getMessage());
        }
        return;
    }
}
