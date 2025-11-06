<?php

use Beeralex\Api\UrlHelper;
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

    /**
     * Получаем меню по символьному коду раздела верхнего уровня
     */
    protected function getMenu(): array
    {
        $menuIblock = IblockHelper::getElementApiTableByCode('menu');
        $sectionCode = $this->arParams['MENU_TYPE'] ?? 'top_menu';
        // Находим раздел меню (например, top_menu или bottom_menu)
        $menuSection = SectionTable::getList([
            'select' => ['ID', 'NAME', 'CODE'],
            'filter' => [
                'IBLOCK_ID' => IblockHelper::getIblockIdByCode('menu'),
                '=CODE' => $sectionCode,
                'ACTIVE' => 'Y',
            ],
            'limit' => 1,
        ])->fetch();

        if (!$menuSection) {
            return [];
        }

        // Получаем элементы этого раздела
        $menuItems = $menuIblock::query()
            ->setSelect(['ID', 'NAME', 'CODE', 'LINK_VALUE' => 'LINK.VALUE', 'IBLOCK_ID_LINK_VALUE' => 'IBLOCK_ID_LINK.VALUE'])
            ->setFilter([
                'ACTIVE' => 'Y',
                '=IBLOCK_SECTION_ID' => $menuSection['ID'],
            ])
            ->setOrder(['SORT' => 'ASC'])
            ->exec()
            ->fetchAll();
        $result = [];
        foreach ($menuItems as $item) {
            $link = trim($item['LINK_VALUE'] ?? '');
            $children = [];

            if (!empty($item['IBLOCK_ID_LINK_VALUE'])) {
                $children = $this->buildMenuFromIblockSections((int)$item['IBLOCK_ID_LINK_VALUE']);
            }

            $result[] = [
                'NAME' => $item['NAME'],
                'LINK' => $link ?: '#',
                'CHILDREN' => $children,
            ];
        }

        return $result;
    }

    /**
     * Формирует вложенные пункты меню на основе разделов указанного инфоблока
     */
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

        // Строим древовидную структуру
        $tree = [];
        $byId = [];

        foreach ($sections as $section) {
            $section['LINK'] = UrlHelper::getSectionUrl(
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
