<?php

namespace Beeralex\Catalog\Location;

use Beeralex\Catalog\Location\Contracts\LocationApiClientContract;
use Beeralex\Catalog\Location\Contracts\BitrixLocationResolverContract;
use Beeralex\Core\Helpers\LocationHelper;
use Beeralex\Core\Traits\Cacheable;
use Psr\Log\LoggerInterface;
use Bitrix\Main\Web\Json;
use Beeralex\Core\Dto\CacheSettingsDto;

class BitrixLocationResolver implements BitrixLocationResolverContract
{
    use Cacheable;

    private LocationApiClientContract $client;
    private LoggerInterface $logger;

    public function __construct(LocationApiClientContract $client, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->logger = $logger;
    }
    
    public function getBitrixLocationByAddress(string|array $location): ?array
    {
        $cacheKey = is_string($location) ? $location : Json::encode($location);
        $cacheSettings = new CacheSettingsDto(BitrixLocationResolverContract::CACHE_TIME, md5($cacheKey), 'dadata/location');
        try {
            return $this->getCached($cacheSettings, function () use ($location) {
                $variants = $this->getVariantsFromLocation($location);
                if ($variants === null) {
                    return null;
                }
                [$settlementVariants, $cityVariants, $areaVariants, $regionVariants] = $variants;
                $foundItems = $this->searchPriority([$settlementVariants, $cityVariants, $areaVariants, $regionVariants,]);
                if (empty($foundItems)) {
                    return null;
                }
                $matched = $this->matchRegionAndArea($foundItems, $regionVariants, $areaVariants);
                $final = $matched ?: reset($foundItems);
                return ['city' => $final['DISPLAY'] ?? $cityVariants[0] ?? null, 'code' => $final['CODE'] ?? null, 'area' => $areaVariants[0] ?? null, 'region' => $regionVariants[0] ?? null,];
            });
        } catch (\Throwable $e) {
            $this->logger->error("BitrixLocationResolver error: " . $e->getMessage());
            return null;
        }
    }

    private function getVariantsFromLocation(string|array $location): ?array
    {
        if (is_string($location)) {
            $suggestions = $this->client->suggestAddress($location, 5);
        } elseif (is_array($location)) {
            $lat = $location['lat'] ?? $location['latitude'] ?? $location[0] ?? null;
            $lon = $location['lon'] ?? $location['longitude'] ?? $location[1] ?? null;
            if (!$lat || !$lon) {
                throw new \InvalidArgumentException('Invalid array $location');
            }
            $suggestions = $this->client->geolocate($lat, $lon, 100, 3);
        } else {
            return null;
        }
        foreach ($suggestions as $s) {
            if (!isset($s['data'])) {
                continue;
            }
            $variants = $this->parseSuggestion($s['data']);
            if (!empty($variants[0]) || !empty($variants[1])) {
                return $variants;
            }
        }
        return null;
    }

    private function parseSuggestion(array $data): array
    {
        return [
            $this->makeName($data['settlement'] ?? null, $data['settlement_type'] ?? '', $data['settlement_type_full'] ?? ''),
            $this->makeName($data['city'] ?? null, $data['city_type'] ?? '', $data['city_type_full'] ?? ''),
            $this->makeName($data['area'] ?? null, $data['area_type'] ?? '', $data['area_type_full'] ?? ''),
            ($data['region'] ?? null) !== ($data['city'] ?? null) ? $this->makeName($data['region'] ?? null, $data['region_type'] ?? '', $data['region_type_full'] ?? '') : [],
        ];
    }

    private function makeName(?string $base, string $type, string $typeFull): array
    {
        if (!$base) return [];
        $variants = [trim("$base $typeFull"), trim("$typeFull $base"), trim("$base $type"), trim("$type $base"), $base,];
        if ($typeFull === 'город') {
            array_unshift($variants, $base);
        }
        return array_unique(array_filter($variants));
    }

    private function searchPriority(array $groups): array
    {
        foreach ($groups as $variants) {
            $items = $this->searchInBitrix($variants);
            if (!empty($items)) {
                return $items;
            }
        }
        return [];
    }

    private function searchInBitrix(array $variants): array
    {
        foreach ($variants as $variant) {
            $variant = trim(mb_strtolower($variant));
            if ($variant === '') continue;
            $items = LocationHelper::find($variant, 20, 0);
            if (!empty($items)) {
                return $items;
            }
        }
        return [];
    }

    private function matchRegionAndArea(array $items, array $regionVariants, array $areaVariants): ?array
    {
        foreach ($regionVariants as $regionVariant) {
            $regionLower = mb_strtolower(trim($regionVariant));
            if ($regionLower === '') continue;
            foreach ($items as $item) {
                foreach ($item['PATH'] ?? [] as $path) {
                    if (isset($path['DISPLAY']) && str_contains(mb_strtolower($path['DISPLAY']), $regionLower)) {
                        if ($this->matchArea($item, $areaVariants)) {
                            return $item;
                        }
                        return $item;
                    }
                }
            }
        }
        return null;
    }
    
    private function matchArea(array $item, array $areaVariants): bool
    {
        foreach ($areaVariants as $areaVariant) {
            $areaLower = mb_strtolower(trim($areaVariant));
            if ($areaLower === '') continue;
            foreach ($item['PATH'] ?? [] as $path) {
                if (isset($path['DISPLAY']) && str_contains(mb_strtolower($path['DISPLAY']), $areaLower)) {
                    return true;
                }
            }
        }
        return false;
    }
}
