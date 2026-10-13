<?php

namespace Beeralex\Api\Domain\Iblock\Catalog;

use Beeralex\Core\Http\Resources\Resource;

/**
 * Легковесный DTO товара для поисковых подсказок.
 * Содержит только поля, необходимые для отображения в саджесте.
 *
 * @property int    $id
 * @property string $name
 * @property string $detailPageUrl
 * @property string $previewPictureSrc
 * @property string $previewText
 */
class SearchProductDTO extends Resource
{
    public static function make(array $item): static
    {
        return new static([
            'id'                => (int)($item['ID'] ?? 0),
            'name'              => (string)($item['NAME'] ?? ''),
            'detailPageUrl'     => (string)($item['DETAIL_PAGE_URL'] ?? ''),
            'previewPictureSrc' => (string)($item['PREVIEW_PICTURE_SRC'] ?? ''),
            'previewText'       => (string)($item['PREVIEW_TEXT'] ?? ''),
        ]);
    }
}
