<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Client;

use Beeralex\Apiship\Options;
use Beeralex\Apiship\Sdk\ExtendedApiship;

/**
 * Фабрика клиента ApiShip.
 *
 * Создаёт настроенный ExtendedApiship (фасад над SDK) с токен-адаптером,
 * используя настройки модуля (токен из .env, тест/прод, SSL, кастомный URL).
 * Экземпляр кэшируется в пределах запроса.
 */
final class ClientFactory
{
    private ?ExtendedApiship $client = null;

    public function __construct(
        private readonly Options $options
    ) {}

    /**
     * Возвращает готовый к работе клиент ApiShip.
     */
    public function getClient(): ExtendedApiship
    {
        if ($this->client === null) {
            $adapter = new TokenAdapter(
                accessToken: $this->options->token,
                test: $this->options->isTest,
                sslVerify: $this->options->sslVerify,
                customUrl: $this->options->customApiUrl ?: null,
            );

            $this->client = new ExtendedApiship($adapter);
        }

        return $this->client;
    }
}
