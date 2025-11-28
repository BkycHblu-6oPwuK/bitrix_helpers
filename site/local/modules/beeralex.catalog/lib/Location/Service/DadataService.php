<?php

namespace Beeralex\Catalog\Location\Service;

use Dadata\DadataClient;
use Beeralex\Catalog\Location\Contracts\LocationApiClientContract;
use Beeralex\Catalog\Location\Contracts\LocationDataParserContract;
use Beeralex\Catalog\Location\Service\Parser\DadataLocationParser;

class DadataService implements LocationApiClientContract
{
    protected readonly DadataClient $client;
    
    public function __construct(string $apiKey, string $secretKey)
    {
        $this->client = new DadataClient($apiKey, $secretKey);
    }
    public function suggestAddress(string $query, int $count = 5): array
    {
        return $this->client->suggest("address", $query, $count);
    }
    public function geolocate(float $lat, float $lon, int $radius = 100, int $count = 3): array
    {
        return $this->client->geolocate("address", $lat, $lon, $radius, $count);
    }

    public function getParser(): ?LocationDataParserContract
    {
        return new DadataLocationParser();
    }
}
