<?php

namespace Beeralex\Api\Domain\Iblock;

use Beeralex\Api\Domain\Iblock\PropertyItemDTO;
use Beeralex\Core\Http\Resources\Resource;

/**
 * Базовая DTO для элемента инфоблока
 */
abstract class AbstractIblockItemDTO extends Resource
{
    /**
     * Поместит свойства если в выборке свойств был IBLOCK_PROPERTY_ID
     * обычно такая структура при декомпозиции в выборке через ORM
     */
    public static function getFromDecomposeProperties(array $item): array
    {
        $properties = [];
        foreach ($item as $key => $value) {
            if (in_array($key, ['PRESELECTED_OFFER', 'OFFERS', 'PRICE', 'CATALOG', 'PRODUCT'])) continue; // Пропускаем, если это предвыбранное предложение
            if (is_array($value)) {
                if (isset($value['IBLOCK_PROPERTY_ID'])) {
                    $properties[$key] = PropertyItemDTO::makeFromDecomposeData($value, $key);
                } else {
                    foreach ($value as $subValue) {
                        if (is_array($subValue) && isset($subValue['IBLOCK_PROPERTY_ID'])) {
                            $properties[$key][] = PropertyItemDTO::makeFromDecomposeData($subValue, $key);
                        }
                    }
                }
            }
        }
        return $properties;
    }

    public static function getFromDisplayProperties(array $item): array
    {
        $properties = [];
        if (!empty($item['DISPLAY_PROPERTIES'])) {
            foreach ($item['DISPLAY_PROPERTIES'] as $prop) {
                $properties[] = PropertyItemDTO::make($prop);
            }
        }
        return $properties;
    }
}
