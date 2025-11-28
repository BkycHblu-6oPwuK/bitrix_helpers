<?php

namespace Beeralex\Catalog\Location\Service\Parser;

use Beeralex\Catalog\Location\Contracts\LocationDataParserContract;

class DadataLocationParser implements LocationDataParserContract
{
    /**
     * Parses the data from the location API into a structured array of variants.
     */
    public function parse(array $suggestions): array
    {
        $getVariants = function (?string $settlement, ?string $city, ?string $area, ?string $region) {
            $settlementVariants = $this->makeName($settlement, 'населенный пункт', 'пункт');
            $cityVariants = $this->makeName($city, 'город', 'город');
            $areaVariants = $this->makeName($area, 'район', 'район');
            $regionVariants = $this->makeName($region, 'область', 'область');
            return [$settlementVariants, $cityVariants, $areaVariants, $regionVariants,];
        };
        foreach ($suggestions as $s) {
            if (!isset($s['data'])) {
                continue;
            }
            $variants = $getVariants($s['data']['settlement'] ?? null, $s['data']['city'] ?? null, $s['data']['area'] ?? null, $s['data']['region'] ?? null);
            if (!empty($variants[0]) || !empty($variants[1])) {
                return $variants;
            }
        }
        return [[], [], [], []];
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
}
