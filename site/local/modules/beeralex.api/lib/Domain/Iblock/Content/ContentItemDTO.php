<?php
declare(strict_types=1);

namespace Beeralex\Api\Domain\Iblock\Content;

use Beeralex\Api\Domain\Iblock\Content\Enum\ContentTypes;

class ContentItemDTO
{
    public function __construct(
        public ContentTypes $type,
        public array|object $result,
    ) {}
}
