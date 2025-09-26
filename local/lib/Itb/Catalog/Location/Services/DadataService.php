<?php

namespace Itb\Catalog\Location\Services;

use Bitrix\Main\Web\Json;
use Dadata\DadataClient;
use Itb\Catalog\Location\Contracts\BitrixLocationResolverInterface;
use Itb\Core\Config;
use Itb\Core\Entity\CacheSettings;
use Itb\Core\Helpers\LocationHelper;
use Itb\Core\Logger\FileLogger;
use Itb\Core\Traits\Cacheable;
use Psr\Log\LoggerInterface;

class DadataService implements BitrixLocationResolverInterface
{
    use Cacheable;

    public readonly DadataClient $client;
    protected readonly LoggerInterface $logger;

    public function __construct()
    {
        $config = Config::getInstance();
        if (!$config->dadataApiKey || !$config->dadataSecretKey) throw new \RuntimeException('Not found dadataApiKey or dadataSecretKey');
        $this->client = new DadataClient($config->dadataApiKey, $config->dadataSecretKey);
        $this->logger = new FileLogger($_SERVER['DOCUMENT_ROOT'] . '/local/logs/dadata_service.log');
    }

    /**
     * для хорошего поиска нужно сделать импорт местоположений до сёл
     * string|array $location - строка адреса либо массив с координатами
     *   Пример: "Москва, Тверская улица"
     *   Пример: ['lat' => 55.76, 'lon' => 37.64] или [55.76, 37.64]
     * @return null|array ['city' => string, 'code' => int] 
     */
    public function getBitrixLocationByAddress(string|array $location): ?array
    {
        $cacheKey = is_string($location) ? $location : Json::encode($location);
        $cacheSettings = new CacheSettings(BitrixLocationResolverInterface::CACHE_TIME, md5($cacheKey), 'dadata/location');

        try {
            return $this->getCached($cacheSettings, function () use ($location) {
                $result = null;
                $cityVariants = $areaVariants = $regionVariants = [];
                $foundItems = [];

                $makeName = static function ($base, $type, $typeFull): array {
                    if (!$base) {
                        return [];
                    }
                    if ($typeFull === 'город') {
                        return [
                            $base,
                            trim("$base $typeFull"),
                            trim("$typeFull $base"),
                            trim("$base $type"),
                            trim("$type $base"),
                        ];
                    }
                    return [
                        trim("$base $typeFull"),
                        trim("$typeFull $base"),
                        trim("$base $type"),
                        trim("$type $base"),
                        $base,
                    ];
                };

                $parseSuggestion = static function (array $s) use ($makeName): array {
                    $settlementVariants = $cityVariants = $areaVariants = $regionVariants = [];
                    if ($s['settlement']) {
                        $settlementVariants = $makeName($s['settlement'], $s['settlement_type'], $s['settlement_type_full']);
                    }

                    if ($s['city']) {
                        $cityVariants = $makeName($s['city'], $s['city_type'], $s['city_type_full']);
                    }

                    if ($s['area']) {
                        $areaVariants = $makeName($s['area'], $s['area_type'], $s['area_type_full']);
                    }

                    if ($s['region'] && $s['city'] != $s['region']) {
                        $regionVariants = $makeName($s['region'], $s['region_type'], $s['region_type_full']);
                    }

                    return [$settlementVariants, $cityVariants, $areaVariants, $regionVariants];
                };


                $searchInBitrix = static function (array $variants): array {
                    foreach ($variants as $variant) {
                        $variant = trim(mb_strtolower($variant));
                        if ($variant === '') continue;

                        $items = LocationHelper::find($variant, 20, 0);
                        if (!empty($items)) {
                            return $items;
                        }
                    }
                    return [];
                };

                $matchRegionAndArea = static function (array $items, array $regionVariants, array $areaVariants): ?array {
                    $matched = null;
                    foreach ($regionVariants as $regionVariant) {
                        $regionLower = mb_strtolower(trim($regionVariant));
                        if ($regionLower === '') continue;

                        foreach ($items as $item) {
                            foreach ($item['PATH'] ?? [] as $path) {
                                if (isset($path['DISPLAY']) && mb_strpos(mb_strtolower($path['DISPLAY']), $regionLower) !== false) {
                                    $matched = $item;
                                    break 2;
                                }
                            }
                        }
                    }

                    if ($matched && !empty($areaVariants)) {
                        foreach ($areaVariants as $areaVariant) {
                            $areaLower = mb_strtolower(trim($areaVariant));
                            if ($areaLower === '') continue;

                            foreach ($matched['PATH'] ?? [] as $path) {
                                if (isset($path['DISPLAY']) && mb_strpos(mb_strtolower($path['DISPLAY']), $areaLower) !== false) {
                                    return $matched;
                                }
                            }
                        }
                    }

                    return $matched;
                };
                if (is_string($location)) {
                    $suggestions = $this->client->suggest("address", $location, 5);

                    foreach ($suggestions as $s) {
                        if (!isset($s['data'])) {
                            continue;
                        }

                        [$settlementVariants, $cityVariants, $areaVariants, $regionVariants] = $parseSuggestion($s['data']);

                        if (!empty($cityVariants) || !empty($settlementVariants)) {
                            break;
                        }
                    }
                } elseif (is_array($location)) {
                    $lat = $location['lat'] ?? $location['latitude'] ?? $location[0] ?? null;
                    $lon = $location['lon'] ?? $location['longitude'] ?? $location[1] ?? null;

                    if ($lat && $lon) {
                        $suggestions = $this->client->geolocate("address", $lat, $lon, 100, 3);
                        foreach ($suggestions as $s) {
                            [$settlementVariants, $cityVariants, $areaVariants, $regionVariants] = $parseSuggestion($s['data']);
                            if ($settlementVariants || $cityVariants) break;
                        }
                    } else {
                        throw new \Exception('Invalid array $location');
                    }
                }
                $foundItems = $searchInBitrix($settlementVariants);
                if (empty($foundItems)) {
                    $foundItems = $searchInBitrix($cityVariants);
                    if (empty($foundItems)) {
                        $foundItems = $searchInBitrix($areaVariants);
                        if (empty($foundItems)) {
                            $foundItems = $searchInBitrix($regionVariants);
                        }
                    }
                }
                if (!empty($foundItems)) {
                    $matched = $matchRegionAndArea($foundItems, $regionVariants, $areaVariants);
                    $final = $matched ?: reset($foundItems);

                    $result = [
                        'city'   => $final['DISPLAY'] ?? (is_array($cityVariants) ? reset($cityVariants) : $cityVariants),
                        'code'   => $final['CODE'] ?? null,
                        'area'   => is_array($areaVariants) ? reset($areaVariants) : $areaVariants,
                        'region' => is_array($regionVariants) ? reset($regionVariants) : $regionVariants,
                    ];
                }

                return $result;
            });
        } catch (\Throwable $e) {
            $this->logger->error("Dadata error: " . $e->getMessage());
        }

        return null;
    }
}
