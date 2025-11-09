<?php
declare(strict_types=1);

namespace Beeralex\Api\Domain\Pagination;

use Beeralex\Core\Helpers\PaginationHelper;

class PaginationDTO
{
    public function __construct(
        public array $pages = [],
        public int $pageSize = 0,
        public int $currentPage = 1,
        public int $pageCount = 0,
        public string $paginationUrlParam = '',
    ){}

    public static function fromResult(\CIBlockResult $nav, int $pageWindow = 5) : static
    {
        $pagination = PaginationHelper::toArray($nav, $pageWindow);
        return new static($pagination['pages'], $pagination['pageSize'], $pagination['currentPage'], $pagination['pageCount'], $pagination['paginationUrlParam']);
    }
}