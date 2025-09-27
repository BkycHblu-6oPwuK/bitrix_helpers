<?php

namespace Itb\Catalog\Location\Services;

use Dadata\DadataClient;
use Itb\Catalog\Location\Contracts\LocationApiClientInterface;

class DadataService implements LocationApiClientInterface
{
    private DadataClient $client;
    
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
}
