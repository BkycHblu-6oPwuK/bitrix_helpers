<?php

namespace Beeralex\Catalog\Location;

use Beeralex\Catalog\Dto\LocationDTO;
use Beeralex\Catalog\Location\Contracts\LocationApiClientContract;
use Beeralex\Catalog\Location\Contracts\BitrixLocationResolverContract;
use Beeralex\Core\Traits\Cacheable;
use Bitrix\Main\Web\Json;
use Beeralex\Core\Dto\CacheSettingsDto;
use Beeralex\Core\Service\LocationService;

use function Beeralex\Catalog\log;

class BitrixLocationResolver implements BitrixLocationResolverContract
{
    use Cacheable;

    public function __construct(
        protected readonly LocationApiClientContract $client,
        protected readonly LocationService $locationService,
    ) {}

    /**
     * Возвращает данные местоположения из Битрикс по адресу или координатам
     */
    public function getBitrixLocationByAddress(string|LocationDTO $location): ?array
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
            log("BitrixLocationResolver error: " . $e->getMessage());
            return null;
        }
    }

    private function getVariantsFromLocation(string|LocationDTO $location): ?array
    {
        $parser = $this->client->getParser();
        if ($parser === null) {
            return null;
        }
        if (is_string($location)) {
            $suggestions = $this->client->suggestAddress($location, 5);
        } elseif ($location->latitude && $location->longitude) {
            $suggestions = $this->client->geolocate($location->latitude, $location->longitude, 100, 3);
        } else {
            return null;
        }
        return $parser->parse($suggestions);
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
            $items = $this->locationService->find($variant, 20, 0);
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
