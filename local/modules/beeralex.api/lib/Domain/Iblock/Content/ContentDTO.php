<?php
declare(strict_types=1);

namespace Beeralex\Api\Domain\Iblock\Content;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property string $title
 * @property string $text
 * @property string $pictureSrc
 */
class ContentDTO extends Resource
{
    public static function make(array $data): static
    {
        return new static(
            [
                'title' => (string)$data['NAME'],
                'text' => (string)$data['PREVIEW_TEXT'],
                'pictureSrc' => (string)$data['PICTURE_SRC'],
            ]
        );
    }
}
