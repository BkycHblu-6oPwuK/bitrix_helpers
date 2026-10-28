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
     * @return PropertyItemDTO[]
     */
    public static function getFromDecomposeProperties(array $item): array
    {
        $skip = ['PRESELECTED_OFFER', 'OFFERS', 'PRICE', 'CATALOG', 'PRODUCT', 'IBLOCK_MODEL_SECTION'];
        $properties = [];

        foreach ($item as $key => $value) {
            if (!is_array($value) || in_array($key, $skip, true)) {
                continue;
            }
            if ($dto = PropertyItemDTO::makeFromDecomposeData($value, (string)$key)) {
                $properties[$key] = $dto;
            }
        }

        return $properties;
    }

    /**
     * @return PropertyItemDTO[]
     */
    public static function getFromDisplayProperties(array $item): array
    {
        $properties = [];
        if (!empty($item['DISPLAY_PROPERTIES'])) {
            foreach ($item['DISPLAY_PROPERTIES'] as $prop) {
                $properties[$prop['CODE']] = PropertyItemDTO::make($prop);
            }
        }
        return $properties;
    }
}
