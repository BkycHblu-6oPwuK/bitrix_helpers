<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Client;

use Apiship\Adapter\GuzzleAdapter;
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
            $adapter = new GuzzleAdapter(
                login: $this->options->login,
                password: $this->options->password,
                test: $this->options->isTest,
            );

            $this->client = new ExtendedApiship($adapter);
        }

        return $this->client;
    }
}
