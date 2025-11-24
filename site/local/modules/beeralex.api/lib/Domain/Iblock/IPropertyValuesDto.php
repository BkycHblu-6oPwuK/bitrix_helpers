<?php
namespace Beeralex\Api\Domain\Iblock;

use Beeralex\Core\Http\Resources\Resource;

/** 
 * @property string $elementMetaTitle
 * @property string $elementMetaKeywords
 * @property string $elementMetaDescription
 * @property string $elementDetailPictureFileTitle
 * @property string $elementDetailPictureFileName
 * @property string $elementDetailPictureFileAlt
 * @property string $elementPreviewPictureFileTitle
 * @property string $sectionMetaDescription
 * @property string $elementPreviewPictureFileAlt
 * @property string $sectionMetaKeywords
 * @property string $sectionMetaTitle
 * @property string $elementPreviewPictureFileName
 * @property string $sectionPageTitle
 * @property string $elementPageTitle
 * @property string $sectionPictureFileAlt
 * @property string $sectionPictureFileTitle
 * @property string $sectionDetailPictureFileAlt
 * @property string $sectionDetailPictureFileTitle
 * @property string $sectionPictureFileName
 * @property string $sectionDetailPictureFileName
*/
class IPropertyValuesDTO extends Resource 
{
    public static function make(array $propertyValues): static
    {
        return new static([
            'elementMetaTitle' => $propertyValues['ELEMENT_META_TITLE'] ?? '',
            'elementMetaKeywords' => $propertyValues['ELEMENT_META_KEYWORDS'] ?? '',
            'elementMetaDescription' => $propertyValues['ELEMENT_META_DESCRIPTION'] ?? '',
            'elementDetailPictureFileTitle' => $propertyValues['ELEMENT_DETAIL_PICTURE_FILE_TITLE'] ?? '',
            'elementDetailPictureFileName' => $propertyValues['ELEMENT_DETAIL_PICTURE_FILE_NAME'] ?? '',
            'elementDetailPictureFileAlt' => $propertyValues['ELEMENT_DETAIL_PICTURE_FILE_ALT'] ?? '',
            'elementPreviewPictureFileTitle' => $propertyValues['ELEMENT_PREVIEW_PICTURE_FILE_TITLE'] ?? '',
            'sectionMetaDescription' => $propertyValues['SECTION_META_DESCRIPTION'] ?? '',
            'elementPreviewPictureFileAlt' => $propertyValues['ELEMENT_PREVIEW_PICTURE_FILE_ALT'] ?? '',
            'sectionMetaKeywords' => $propertyValues['SECTION_META_KEYWORDS'] ?? '',
            'sectionMetaTitle' => $propertyValues['SECTION_META_TITLE'] ?? '',
            'elementPreviewPictureFileName' => $propertyValues['ELEMENT_PREVIEW_PICTURE_FILE_NAME'] ?? '',
            'sectionPageTitle' => $propertyValues['SECTION_PAGE_TITLE'] ?? '',
            'elementPageTitle' => $propertyValues['ELEMENT_PAGE_TITLE'] ?? '',
            'sectionPictureFileAlt' => $propertyValues['SECTION_PICTURE_FILE_ALT'] ?? '',
            'sectionPictureFileTitle' => $propertyValues['SECTION_PICTURE_FILE_TITLE'] ?? '',
            'sectionDetailPictureFileAlt' => $propertyValues['SECTION_DETAIL_PICTURE_FILE_ALT'] ?? '',
            'sectionDetailPictureFileTitle' => $propertyValues['SECTION_DETAIL_PICTURE_FILE_TITLE'] ?? '',
            'sectionPictureFileName' => $propertyValues['SECTION_PICTURE_FILE_NAME'] ?? '',
            'sectionDetailPictureFileName' => $propertyValues['SECTION_DETAIL_PICTURE_FILE_NAME'] ?? '',
        ]);
    }
}