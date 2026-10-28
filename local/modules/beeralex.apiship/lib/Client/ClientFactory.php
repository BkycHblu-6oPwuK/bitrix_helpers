<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Client;

use Apiship\Adapter\GuzzleAdapter;
use Beeralex\Apiship\Exception\ApiShipException;
use Beeralex\Apiship\Options;
use Beeralex\Apiship\Sdk\ExtendedApiship;

final class ClientFactory
{
    private ?ExtendedApiship $client = null;

    public function __construct(
        private readonly Options $options
    ) {}

    /**
     * @throws ApiShipException если логин/пароль не заданы
     */
    public function getClient(): ExtendedApiship
    {
        if ($this->client === null) {
            try {
                $adapter = new GuzzleAdapter(
                    login: $this->options->login,
                    password: $this->options->password,
                    test: $this->options->isTest,
                );
            } catch (\InvalidArgumentException $e) {
                throw new ApiShipException('Не настроены логин или пароль ApiShip', 0, $e);
            }

            $this->client = new ExtendedApiship($adapter);
        }

        return $this->client;
    }
}
