<?php

declare(strict_types=1);

namespace Beeralex\Api\Domain\Iblock;

use Beeralex\Core\Service\UrlService;

/**
 * @property int $id
 * @property string $name
 * @property string $code
 * @property string $previewText
 * @property string $detailText
 * @property string $previewPictureSrc
 * @property string $detailPictureSrc
 * @property string $detailPageUrl
 * @property string $listPageUrl
 * @property string $dateCreate
 * @property PropertyItemDTO[] $properties
 * DTO для элементов инфоблока, которые могут быть использованы в списках новостей
 */
class ElementDTO extends AbstractIblockItemDTO
{
    public static function make(array $data): static
    {
        $props = static::getFromDisplayProperties($data);
        return new static([
            'id' => (int)$data['ID'],
            'name' => (string)$data['NAME'],
            'code' => (string)$data['CODE'],
            'previewText' => $data['PREVIEW_TEXT'] ?? '',
            'detailText' => $data['DETAIL_TEXT'] ?? '',
            'previewPictureSrc' => $data['PREVIEW_PICTURE']['SRC'] ?? '',
            'detailPictureSrc' => $data['DETAIL_PICTURE']['SRC'] ?? '',
            'detailPageUrl' => $data['DETAIL_PAGE_URL'] ? service(UrlService::class)->cleanUrl($data['DETAIL_PAGE_URL']) : '',
            'listPageUrl' => $data['LIST_PAGE_URL'] ? service(UrlService::class)->cleanUrl($data['LIST_PAGE_URL']) : '',
            'dateCreate' => $data['DATE_CREATE'] ?? '',
            'properties' => $props,
        ]);
    }

    public static function fromNewsListElement(array $element): static
    {
        $props = static::getFromDisplayProperties($element);

        return new static([
            'id' => (int)$element['ID'],
            'name' => (string)$element['NAME'],
            'code' => (string)$element['CODE'],
            'previewText' => $element['PREVIEW_TEXT'] ?? '',
            'detailText' => $element['DETAIL_TEXT'] ?? '',
            'previewPictureSrc' => $element['PREVIEW_PICTURE']['SRC'] ?? '',
            'detailPictureSrc' => $element['DETAIL_PICTURE']['SRC'] ?? '',
            'detailPageUrl' => $element['DETAIL_PAGE_URL'] ? service(UrlService::class)->cleanUrl($element['DETAIL_PAGE_URL']) : '',
            'dateCreate' => $element['DATE_CREATE'] ?? '',
            'properties' => $props,
        ]);
    }

    public static function fromNewsDetailElement(array $element): static
    {
        $props = static::getFromDisplayProperties($element);

        return new static([
            'id' => (int)$element['ID'],
            'name' => (string)$element['NAME'],
            'code' => (string)$element['CODE'],
            'detailText' => $element['DETAIL_TEXT'] ?? '',
            'previewText' => $element['PREVIEW_TEXT'] ?? '',
            'detailPictureSrc' => $element['DETAIL_PICTURE']['SRC'] ?? '',
            'previewPictureSrc' => $element['PREVIEW_PICTURE']['SRC'] ?? '',
            'listPageUrl' => $element['LIST_PAGE_URL'] ? service(UrlService::class)->cleanUrl($element['LIST_PAGE_URL']) : '',
            'dateCreate' => $element['DATE_CREATE'] ?? '',
            'properties' => $props,
        ]);
    }

    public static function fromDecomposeData(array $data): static
    {
        $props = static::getFromDecomposeProperties($data);
        return new static([
            'id' => (int)$data['ID'],
            'name' => (string)$data['NAME'],
            'code' => (string)$data['CODE'],
            'previewText' => $data['PREVIEW_TEXT'] ?? '',
            'detailText' => $data['DETAIL_TEXT'] ?? '',
            'previewPictureSrc' => $data['PREVIEW_PICTURE']['SRC'] ?? '',
            'detailPictureSrc' => $data['DETAIL_PICTURE']['SRC'] ?? '',
            'detailPageUrl' => $data['DETAIL_PAGE_URL'] ? service(UrlService::class)->cleanUrl($data['DETAIL_PAGE_URL']) : '',
            'listPageUrl' => $data['LIST_PAGE_URL'] ? service(UrlService::class)->cleanUrl($data['LIST_PAGE_URL']) : '',
            'dateCreate' => $data['DATE_CREATE'] ?? '',
            'properties' => $props,
        ]);
    }
}
