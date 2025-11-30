<?php
declare(strict_types=1);

namespace Beeralex\Api\Domain\Iblock\Content;

use Beeralex\Api\Domain\Iblock\ElementDTO;
use Beeralex\Core\Http\Resources\Resource;

/**
 * @property ElementDTO[] $items
 */
class VideoDTO extends Resource
{
    /**
     * @param array{ITEMS: array} $data
     */
    public static function make(array $data): static
    {
        return new static([
            'items' => array_map([ElementDTO::class, 'fromNewsListElement'], $data['ITEMS']),
        ]);
    }
}
