<?php
declare(strict_types=1);

namespace Beeralex\Api\Domain\Iblock;

use Beeralex\Api\UrlService;

class ElementDTO
{
    /**
     * @param PropertyItemDTO[] $properties
     */
    public function __construct(
        public int $id,
        public string $code,
        public string $name,
        public ?string $previewText = null,
        public ?string $previewPicture = null,
        public ?string $detailText = null,
        public ?string $detailPicture = null,
        public ?string $detailPageUrl = null,
        public ?string $listPageUrl = null,
        public ?string $dateCreate = null,
        public array $properties = [],
    ) {}

    public static function fromNewsListElement(array $element): static
    {
        $props = [];
        if (!empty($element['DISPLAY_PROPERTIES'])) {
            foreach ($element['DISPLAY_PROPERTIES'] as $prop) {
                $dto = PropertyItemDTO::fromArray($prop);
                if ($dto) {
                    $props[] = $dto;
                }
            }
        }

        return new static(
            id: (int)$element['ID'],
            code: (string)$element['CODE'],
            name: (string)$element['NAME'],
            previewText: $element['PREVIEW_TEXT'] ?? null,
            previewPicture: $element['PREVIEW_PICTURE']['SRC'] ?? null,
            detailPageUrl: service(UrlService::class)->cleanUrl($element['DETAIL_PAGE_URL']) ?? null,
            dateCreate: $element['DATE_CREATE'] ?? null,
            properties: $props,
        );
    }

    public static function fromNewsDetailElement(array $element): static
    {
        $props = [];
        if (!empty($element['DISPLAY_PROPERTIES'])) {
            foreach ($element['DISPLAY_PROPERTIES'] as $prop) {
                $dto = PropertyItemDTO::fromArray($prop);
                if ($dto) {
                    $props[] = $dto;
                }
            }
        }

        return new static(
            id: (int)$element['ID'],
            code: (string)$element['CODE'],
            name: (string)$element['NAME'],
            detailText: $element['DETAIL_TEXT'] ?? null,
            previewText: $element['PREVIEW_TEXT'] ?? null,
            detailPicture: $element['DETAIL_PICTURE']['SRC'] ?? null,
            previewPicture: $element['PREVIEW_PICTURE']['SRC'] ?? null,
            listPageUrl: service(UrlService::class)->cleanUrl($element['LIST_PAGE_URL']) ?? null,
            dateCreate: $element['DATE_CREATE'] ?? null,
            properties: $props,
        );
    }
}
