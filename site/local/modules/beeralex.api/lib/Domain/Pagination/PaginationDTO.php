<?php
declare(strict_types=1);

namespace Beeralex\Api\Domain\Pagination;

use Beeralex\Core\Service\PaginationService;

/**
 * @property array $pages
 * @property int $pageSize
 * @property int $currentPage
 * @property int $pageCount
 * @property string $paginationUrlParam
 */
class PaginationDTO extends \Beeralex\Core\Http\Resources\Resource
{
    /**
     * @param array{pages?: array, pageSize?: int, currentPage?: int, pageCount?: int, paginationUrlParam?: string} $data
     */
    public static function make(array $data): static
    {
        return new static([
            'pages' => $data['pages'] ?? [],
            'pageSize' => $data['pageSize'] ?? 0,
            'currentPage' => $data['currentPage'] ?? 1,
            'pageCount' => $data['pageCount'] ?? 0,
            'paginationUrlParam' => $data['paginationUrlParam'] ?? '',
        ]);
    }

    public static function fromResult(\CIBlockResult $nav, int $pageWindow = 5) : static
    {
        $pagination = service(PaginationService::class)->toArray($nav, $pageWindow);
        return static::make($pagination);
    }
}