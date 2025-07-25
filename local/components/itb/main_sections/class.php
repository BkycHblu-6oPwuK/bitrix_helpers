<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\FileTable;
use Bitrix\Main\ORM\Fields\ExpressionField;
use Itb\Core\Helpers\IblockHelper;
use Itb\Helpers\CatalogHelper;

class ItbMainSections extends \CBitrixComponent
{
    public function executeComponent()
    {
        if ($this->startResultCache()) {
            $items = $this->getSections();
            if (empty($items)) {
                $this->abortResultCache();
                return;
            }
            $this->arResult['items'] = $items;
            $this->includeComponentTemplate();
        }
    }

    protected function getSections(): array
    {
        $result = [];
        $count = 0;
        $elementIds = $this->arParams['ELEMENT_IDS'];
        $catalogId = CatalogHelper::getCatalogIblockId();
        $section = \Bitrix\Iblock\Model\Section::compileEntityByIblock($catalogId);

        $elements = collect(
            IblockHelper::getElementApiTableByCode('mainSections')::query()
                ->setSelect([
                    'ID',
                    'LIST_TITLE' => 'LIST_LINKS.VALUE',
                    'LIST_LINK' => 'LIST_LINKS.DESCRIPTION',
                    'SECTION_PAGE_URL' => 'SECTION.IBLOCK.SECTION_PAGE_URL',
                    'SECTION_CODE' => 'SECTION.CODE',
                    'SECTION_ID' => 'CATALOG_RAZDEL.VALUE',
                    'PICTURE_SRC',
                ])
                ->where('SECTION.ACTIVE', 'Y')
                ->whereIn('ID', $elementIds)
                ->registerRuntimeField('SECTION', [
                    'data_type' => $section,
                    'reference' => [
                        '=this.CATALOG_RAZDEL.VALUE' => 'ref.ID',
                    ],
                    'join_type' => 'INNER'
                ])
                ->registerRuntimeField('img', [
                    'data_type' => FileTable::class,
                    'reference' => [
                        '=this.SECTION.PICTURE' => 'ref.ID',
                    ],
                    'join_type' => 'INNER'
                ])
                ->registerRuntimeField('PICTURE_SRC', new ExpressionField(
                    'SRC',
                    "CONCAT('upload/',iblock_elements_element_main_sections_api_img.SUBDIR, '/', iblock_elements_element_main_sections_api_img.FILE_NAME)"
                ))
                ->fetchAll()
        )->map(function ($item) {
            $this->replaceUrl($item);
            return $item;
        })->groupBy('ID')->toArray();

        foreach ($elementIds as $id) {
            $id = (int)$id;
            if (!isset($elements[(string)(int)$id])) {
                continue;
            }

            foreach ($elements[$id] as $item) {
                if (!isset($result[$id])) {
                    $count++;
                    $result[$id] = [
                        'img' => $item['PICTURE_SRC'] ?? '',
                        'link' => $item['SECTION_URL'] ?? '',
                        'list' => []
                    ];
                }
                if (!empty($item['LIST_TITLE']) && !empty($item['LIST_LINK'])) {
                    $result[$id]['list'][] = [
                        'title' => $item['LIST_TITLE'],
                        'link' => $item['LIST_LINK'],
                    ];
                }
            }
            if ($count === 4) break;
        }

        return $result;
    }


    protected function replaceUrl(&$item): void
    {
        $url = \CIBlock::ReplaceSectionUrl($item['SECTION_PAGE_URL'], [
            'SECTION_CODE' => $item['SECTION_CODE'],
            'IBLOCK_SECTION_ID' => $item['SECTION_ID']
        ], false, 'E');
        $item['SECTION_URL'] = $url;
    }
}
