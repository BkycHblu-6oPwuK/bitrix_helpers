<?php
declare(strict_types=1);

namespace Beeralex\Api\Domain\Form\Dto;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property int $id
 * @property string $name
 * @property string $label
 * @property string $type
 * @property bool $required
 * @property bool $isMultiple
 * @property array $attributes
 * @property string $error
 * @property array $options
 */
class FormNewFieldDTO extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'id' => (int)($data['id'] ?? 0),
            'name' => $data['name'] ?? '',
            'label' => $data['label'] ?? '',
            'type' => $data['type'] ?? '',
            'required' => (bool)($data['required'] ?? false),
            'isMultiple' => (bool)($data['isMultiple'] ?? false),
            'attributes' => $data['attributes'] ?? [],
            'error' => $data['error'] ?? '',
            'options' => $data['options'] ?? [],
        ]);
    }
}