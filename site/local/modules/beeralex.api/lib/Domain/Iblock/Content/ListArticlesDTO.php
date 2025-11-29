<?php
declare(strict_types=1);

namespace Beeralex\Api\Domain\Iblock\Content;

use Beeralex\Api\Domain\Iblock\ElementDTO;
use Beeralex\Core\Http\Resources\Resource;

/**
 * @property string $link
 * @property ElementDTO[] $items
 */
class ListArticlesDTO extends Resource
{
    /**
     * @param array{LINK: string, ITEMS: array} $data
     */
    public static function make(array $data): static
    {
        return new static([
            'link' => $data['LINK'] ?? '',
            'items' => array_map([ElementDTO::class, 'fromNewsListElement'], $data['ITEMS']),
        ]);
    }
}
