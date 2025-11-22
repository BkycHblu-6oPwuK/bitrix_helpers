<?php

use Beeralex\Api\UrlService;
use Bitrix\Main\Loader;
use Bitrix\Iblock\SectionTable;
use Beeralex\Core\Helpers\IblockHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class BeeralexMenu extends CBitrixComponent
{
    /** @inheritDoc */
    public function executeComponent()
    {
        if ($this->startResultCache()) {
            Loader::requireModule('iblock');
            $this->arResult['MENU'] = $this->getMenu();
            $this->includeComponentTemplate();
        }

        return $this->arResult['MENU'];
    }

    protected function getMenu(): array
    {
        $menuIblock = IblockHelper::getElementApiTableByCode('menu');
        $sectionCode = $this->arParams['MENU_TYPE'] ?? 'top_menu';
        $menuIblockId = IblockHelper::getIblockIdByCode('menu');

        // Берём верхний раздел меню
        $menuSection = SectionTable::getList([
            'select' => [
                'ID',
                'NAME',
                'CODE',
                'LEFT_MARGIN',
                'RIGHT_MARGIN',
                'IBLOCK_ID',
            ],
            'filter' => [
                'IBLOCK_ID' => $menuIblockId,
                '=CODE' => $sectionCode,
                'ACTIVE' => 'Y',
            ],
            'limit' => 1,
        ])->fetch();

        if (!$menuSection) {
            return [];
        }

        // Получаем все элементы меню (включая вложенные разделы)
        $menuItems = $menuIblock::query()
            ->setSelect([
                'ID',
                'NAME',
                'CODE',
                'LINK_VALUE' => 'LINK.VALUE',
                'IBLOCK_ID_LINK_VALUE' => 'IBLOCK_ID_LINK.VALUE',
                'IBLOCK_SECTION_ID',
                'SECTION_NAME' => 'SECTION.NAME',
                'SECTION_ID' => 'SECTION.ID',
            ])
            ->registerRuntimeField(
                'SECTION',
                [
                    'data_type' => SectionTable::class,
                    'reference' => ['this.IBLOCK_SECTION_ID' => 'ref.ID'],
                    'join_type' => 'inner',
                ]
            )
            ->setFilter([
                'ACTIVE' => 'Y',
                'SECTION.IBLOCK_ID' => $menuSection['IBLOCK_ID'],
                '>=SECTION.LEFT_MARGIN' => $menuSection['LEFT_MARGIN'],
                '<=SECTION.RIGHT_MARGIN' => $menuSection['RIGHT_MARGIN'],
            ])
            ->setOrder(['SECTION.LEFT_MARGIN' => 'ASC', 'SORT' => 'ASC'])
            ->exec()
            ->fetchAll();

        if (!$menuItems) {
            return [];
        }

        $grouped = [];
        
        foreach ($menuItems as $item) {
            $sectionId = (int)$item['SECTION_ID'] ?? 0;
            $sectionName = $item['SECTION_NAME'] ?? null;

            $link = trim($item['LINK_VALUE'] ?? '');
            $children = [];

            if (!empty($item['IBLOCK_ID_LINK_VALUE'])) {
                $children = $this->buildMenuFromIblockSections((int)$item['IBLOCK_ID_LINK_VALUE']);
            }
            
            $element = [
                'NAME' => $item['NAME'],
                'LINK' => $link ?: '#',
                'CHILDREN' => $children,
            ];
            
            if ($sectionId && $sectionName && $sectionId !== (int)$menuSection['ID']) {
                // Вложенный раздел — добавляем как группу
                $grouped[$sectionId]['NAME'] = $sectionName;
                $grouped[$sectionId]['LINK'] = null;
                $grouped[$sectionId]['CHILDREN'][] = $element;
            } else {
                // Элементы верхнего уровня
                $grouped[] = $element;
            }
        }

        // Преобразуем в финальный массив
        $result = array_values($grouped);
        
        return $result;
    }

    protected function buildMenuFromIblockSections(int $iblockId): array
    {
        $sections = SectionTable::getList([
            'select' => ['ID', 'NAME', 'CODE', 'IBLOCK_SECTION_ID', 'SECTION_PAGE_URL' => 'IBLOCK.SECTION_PAGE_URL'],
            'filter' => ['IBLOCK_ID' => $iblockId, 'ACTIVE' => 'Y'],
            'order' => ['LEFT_MARGIN' => 'ASC'],
        ])->fetchAll();

        if (!$sections) {
            return [];
        }

        $tree = [];
        $byId = [];

        foreach ($sections as $section) {
            $section['LINK'] = service(UrlService::class)->getSectionUrl(
                ['CODE' => $section['CODE'], 'ID' => $section['ID']],
                $section['SECTION_PAGE_URL'],
                false,
                'S'
            )['clean_url'];
            $section['CHILDREN'] = [];
            $byId[$section['ID']] = $section;
        }

        foreach ($byId as $id => &$section) {
            if ($section['IBLOCK_SECTION_ID'] && isset($byId[$section['IBLOCK_SECTION_ID']])) {
                $byId[$section['IBLOCK_SECTION_ID']]['CHILDREN'][] = &$section;
            } else {
                $tree[] = &$section;
            }
        }

        return $tree;
    }
}
