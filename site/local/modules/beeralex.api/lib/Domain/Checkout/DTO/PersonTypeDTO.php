<?php
namespace Beeralex\Api\Domain\Checkout\DTO;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property string $selected
 * @property string $oldPersonType
 * @property array $types
 * 
 * DTO типа плательщика
 */
class PersonTypeDTO extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'selected' => $data['selected'] ?? '',
            'oldPersonType' => $data['oldPersonType'] ?? '',
            'types' => $data['types'] ?? [],
        ]);
    }
}