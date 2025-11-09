<?php
declare(strict_types=1);

namespace Beeralex\Api\Domain\Iblock;

class PropertyItemDTO
{
    public function __construct(
        public string $code,
        public string $name,
        public mixed $value,
        public ?string $type = null,
    ) {}

    public static function fromArray(array $property): ?static
    {
        if (empty($property['VALUE']) && empty($property['DISPLAY_VALUE'])) {
            return null;
        }

        $value = null;
        $type = $property['PROPERTY_TYPE'] ?? null;

        if (isset($property['FILE_VALUE']['SRC'])) {
            $value = $property['FILE_VALUE']['SRC'];
        } elseif (is_array($property['VALUE']) && isset($property['VALUE']['TEXT'])) {
            $value = trim((string)$property['VALUE']['TEXT']);
        } else {
            $value = (string)$property['DISPLAY_VALUE'];
        }

        return new static(
            (string)$property['CODE'],
            (string)$property['NAME'],
            $value,
            $type,
        );
    }
}