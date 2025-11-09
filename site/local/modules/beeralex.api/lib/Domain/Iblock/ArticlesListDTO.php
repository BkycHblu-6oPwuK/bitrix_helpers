<?php
declare(strict_types=1);

namespace Beeralex\Api\Domain\Iblock;

use Beeralex\Api\Domain\Pagination\PaginationDTO;

class ArticlesListDTO
{
    /**
     * @param \Beeralex\Api\Domain\Iblock\ElementDTO $articles
     */
    public function __construct(
        public array $articles,
        public ?PaginationDTO $pagination = null,
    ) {}
}
