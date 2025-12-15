<?php
declare(strict_types=1);

namespace Beeralex\Api\Domain\Form\Dto;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property int $id
 * @property string $url
 * @property string $title
 * @property string $imageSrc
 * @property string $description
 * @property string $dateFormat
 * @property FormNewFieldDTO[] $fields
 * @property array $formIdsMap
 * @property string $error
 * @property bool $successAdded
 */
class FormNewDTO extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'id' => (int)($data['id'] ?? 0),
            'url' => $data['url'] ?? '',
            'title' => $data['title'] ?? '',
            'imageSrc' => $data['imageSrc'] ?? '',
            'description' => $data['description'] ?? '',
            'dateFormat' => $data['dateFormat'] ?? '',
            'error' => $data['error'] ?? '',
            'successAdded' => (bool)($data['successAdded'] ?? false),
            'fields' => $data['fields'] ?? [],
            'formIdsMap' => $data['formIdsMap'] ?? [],
        ]);
    }
}
