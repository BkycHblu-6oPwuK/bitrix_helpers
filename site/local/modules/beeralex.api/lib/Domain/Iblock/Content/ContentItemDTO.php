<?php
declare(strict_types=1);

namespace Beeralex\Api\Domain\Iblock\Content;

use Beeralex\Api\Domain\Iblock\Content\Enum\ContentTypes;
use Beeralex\Core\Http\Resources\Resource;

class ContentItemDTO extends Resource
{
    public static function makeFrom(ContentTypes $type, array|object $result): static
    {
        $stored = $result;
        if (is_object($result) && method_exists($result, 'toArray')) {
            $stored = $result->toArray();
        }

        return static::make([
            'type' => $type instanceof ContentTypes ? $type->value : (string)$type,
            'result' => $stored,
        ]);
    }

    public function toArray(): array
    {
        return [
            'type' => $this->type ?? null,
            'result' => $this->result ?? null,
        ];
    }
}
