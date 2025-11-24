<?php
declare(strict_types=1);

namespace Beeralex\Api\Domain\Iblock;

use Beeralex\Api\UrlService;
use Beeralex\Core\Http\Resources\Resource;

/**
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $previewText
 * @property string $detailText
 * @property string $previewPictureSrc
 * @property string $detailPictureSrc
 * @property string $detailPageUrl
 * @property string $listPageUrl
 * @property string $dateCreate
 * @property PropertyItemDTO[] $properties
 */
class ElementDTO extends Resource
{
    public static function make(array $data): static
    {
        throw new \LogicException('Use fromNewsListElement or fromNewsDetailElement methods.');
    }

    public static function fromNewsListElement(array $element): static
    {
        $props = [];
        if (!empty($element['DISPLAY_PROPERTIES'])) {
            foreach ($element['DISPLAY_PROPERTIES'] as $prop) {
                $dto = PropertyItemDTO::make($prop);
                if ($dto) {
                    $props[] = $dto;
                }
            }
        }

        return new static([
            'id' => (int)$element['ID'],
            'code' => (string)$element['CODE'],
            'name' => (string)$element['NAME'],
            'previewText' => $element['PREVIEW_TEXT'] ?? '',
            'detailText' => $element['DETAIL_TEXT'] ?? '',
            'previewPictureSrc' => $element['PREVIEW_PICTURE']['SRC'] ?? '',
            'detailPictureSrc' => $element['DETAIL_PICTURE']['SRC'] ?? '',
            'detailPageUrl' => service(UrlService::class)->cleanUrl($element['DETAIL_PAGE_URL']) ?? '',
            'dateCreate' => $element['DATE_CREATE'] ?? '',
            'properties' => $props,
        ]);
    }

    public static function fromNewsDetailElement(array $element): static
    {
        $props = [];
        if (!empty($element['DISPLAY_PROPERTIES'])) {
            foreach ($element['DISPLAY_PROPERTIES'] as $prop) {
                $dto = PropertyItemDTO::make($prop);
                if ($dto) {
                    $props[] = $dto;
                }
            }
        }

        return new static([
            'id' => (int)$element['ID'],
            'code' => (string)$element['CODE'],
            'name' => (string)$element['NAME'],
            'detailText' => $element['DETAIL_TEXT'] ?? '',
            'previewText' => $element['PREVIEW_TEXT'] ?? '',
            'detailPictureSrc' => $element['DETAIL_PICTURE']['SRC'] ?? '',
            'previewPictureSrc' => $element['PREVIEW_PICTURE']['SRC'] ?? '',
            'listPageUrl' => service(UrlService::class)->cleanUrl($element['LIST_PAGE_URL']) ?? '',
            'dateCreate' => $element['DATE_CREATE'] ?? '',
            'properties' => $props,
        ]);
    }
}
