<?php
declare(strict_types=1);

namespace Beeralex\Api\Domain\Iblock\Content;

use Beeralex\Api\Domain\Iblock\Content\Enum\MainContentTypes;
use Beeralex\Core\Http\Resources\Resource;

/**
 * @property string $type
 * @property Resource $result
 */
class ContentItemDTO extends Resource
{
    public static function make(array $data): static
    {
        throw new \LogicException('Use makeFrom method.');
    }

    public static function makeFrom(MainContentTypes $type, Resource $DTO): static
    {
        return new static([
            'type' => $type instanceof MainContentTypes ? $type->value : (string)$type,
            'result' => $DTO,
        ]);
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type ?? null,
            'result' => $this->result->toArray() ?? null,
        ];
    }
}
