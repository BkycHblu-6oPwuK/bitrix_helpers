<?php
namespace Beeralex\Api\Domain\Checkout\DTO;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $type
 * @property mixed $value
 * @property bool $required
 * @property string $pattern
 * @property bool $isZip
 * @property bool $isLocation
 * @property bool $isPhone
 * @property bool $isEmail
 * @property bool $isAddress
 * @property int $minLength
 * @property int $maxLength
 * @property bool $multiple
 * 
 * DTO свойства заказа
 */
class PropertyDTO extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'id' => $data['id'] ?? 0,
            'code' => $data['code'] ?? '',
            'name' => $data['name'] ?? '',
            'type' => $data['type'] ?? '',
            'value' => $data['value'] ?? null,
            'required' => $data['required'] ?? false,
            'pattern' => $data['pattern'] ?? '',
            'isZip' => $data['isZip'] ?? false,
            'isLocation' => $data['isLocation'] ?? false,
            'isPhone' => $data['isPhone'] ?? false,
            'isEmail' => $data['isEmail'] ?? false,
            'isAddress' => $data['isAddress'] ?? false,
            'minLength' => $data['minLength'] ?? 0,
            'maxLength' => $data['maxLength'] ?? 0,
            'multiple' => $data['multiple'] ?? false,
        ]);
    }
}