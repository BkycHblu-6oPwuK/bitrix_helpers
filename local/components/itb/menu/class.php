<?php

use Bitrix\Iblock\SectionElementTable;
use Bitrix\Iblock\SectionTable;
use Bitrix\Main\DI\ServiceLocator;
use Bitrix\Main\Loader;
use App\Catalog\Helper\CatalogHelper;
use App\Catalog\Type\Contracts\CatalogContextContract;
use App\Iblock\Model\SectionModel;
use App\Main\PageHelper;
use Itb\Core\Helpers\IblockHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class ItbMenu extends CBitrixComponent
{
    protected CatalogContextContract $catalogContext;
    /** @inheritDoc */
    public function executeComponent()
    {
        $this->catalogContext = ServiceLocator::getInstance()->get(CatalogContextContract::class);
        if ($this->startResultCache()) {
            Loader::includeModule('iblock');
            $this->arResult['catalogUrl'] = PageHelper::getCatalogPageUrl() . $this->catalogContext->getRootSectionCode();
            $menu = $this->getMenu();
            $this->arResult['menu'] = $menu;
            // Если шаблон не задан — кешируем только arResult
            $this->getTemplateName() ? $this->includeComponentTemplate() : $this->endResultCache();
        }
        return $this->arResult['menu'];
    }

    /**
     * @return array Общий для всех меню фильтр
     */
    public function getCommonFilter(): array
    {
        return [
            'IBLOCK_ID' => $this->arParams['iblockId'],
            'ACTIVE' => 'Y',
            '=AVAILABLE' => 'Y',
            //'SECTION_ID' => CatalogHelper::getSectionIdByGender($this->arParams['type']),
            'INCLUDE_SUBSECTIONS' => 'Y',
        ];
    }

    protected function getMenu(): array
    {
        $productsIds = $this->getAvailableProductsIds();

        $sectionsIds = $this->getSectionsIdsByProductsIds($productsIds);

        $menu = $this->getSectionMenu();

        $menu = $this->filterMenuBySectionsIds($menu, $sectionsIds);

        $menu = $this->handleMenu($menu);

        return $menu;
    }

    protected function getAvailableProductsIds(): array
    {
        $arFilter = $this->getCommonFilter();

        $dbResult = CIBlockElement::GetList(
            false,
            $arFilter,
            false,
            false,
            ['ID']
        );

        $productsIds = [];
        while ($item = $dbResult->Fetch()) {
            $productsIds[] = $item['ID'];
        }
        return $productsIds;
    }

    /**
     * @return array Все пункты меню
     */
    protected function getSectionMenu(): array
    {
        $catalogId = IblockHelper::getIblockIdByCode('catalog');
        $menu = SectionModel::compileEntityByIblock($catalogId)::query()
            ->setSelect([
                'ID',
                'DEPTH_LEVEL',
                'CODE',
                'NAME',
                'UF_CUSTOM_NAME',
                'UF_MENU_COLUMN',
                'IBLOCK_SECTION_ID',
                'SORT',
                'SECTION_TEMPLATE' => 'iblock.SECTION_PAGE_URL',
                'UF_MENU_ROOT',
            ])
            ->whereNotNull('UF_MENU_COLUMN')
            ->where('GLOBAL_ACTIVE', 'Y')
            ->where('iblock.ACTIVE', 'Y')
            ->where('UF_SHOW_MENU', 1)
            ->setOrder(['LEFT_MARGIN' => 'asc'])
            ->exec()
            ->fetchAll();
        $menu = collect($menu)->keyBy('ID')->toArray();
        foreach ($menu as &$item) {
            if ($item['UF_CUSTOM_NAME']) {
                $item['NAME'] = $item['UF_CUSTOM_NAME'];
            }
            $item['UF_MENU_ROOT'] = $item['UF_MENU_ROOT'] == 1;
        }
        return $menu;
    }

    protected function getSectionsIdsByProductsIds(array $productsIds): array
    {
        $section = SectionTable::query()
            ->setSelect(['ID', 'LEFT_MARGIN', 'RIGHT_MARGIN'])
            //->setFilter(['ID' => CatalogHelper::getSectionIdByGender($this->arParams['type'])])
            ->exec()
            ->fetch();

        $dbResult = SectionElementTable::getList([
            'select' => ['IBLOCK_SECTION_ID'],
            'filter' => [
                'IBLOCK_ELEMENT_ID' => $productsIds,
                '>=IBLOCK_SECTION.LEFT_MARGIN' => $section['LEFT_MARGIN'],
                '<=IBLOCK_SECTION.RIGHT_MARGIN' => $section['RIGHT_MARGIN'],
            ],
        ]);

        $directSectionIds = [];

        foreach ($dbResult->fetchAll() as $item) {
            $directSectionIds[] = $item['IBLOCK_SECTION_ID'];
        }

        $directSectionIds = array_unique($directSectionIds);

        if (empty($directSectionIds)) {
            return [];
        }

        $allSections = SectionTable::getList([
            'select' => ['ID', 'LEFT_MARGIN', 'RIGHT_MARGIN'],
            'filter' => [
                '>=LEFT_MARGIN' => 0,
            ],
            'order' => ['LEFT_MARGIN' => 'ASC'],
        ])->fetchAll();

        $resultSections = [];

        foreach ($directSectionIds as $sectionId) {
            $currentSection = array_filter($allSections, fn($s) => $s['ID'] == $sectionId);
            if ($currentSection) {
                $current = reset($currentSection);
                foreach ($allSections as $possibleParent) {
                    if (
                        $possibleParent['LEFT_MARGIN'] <= $current['LEFT_MARGIN'] &&
                        $possibleParent['RIGHT_MARGIN'] >= $current['RIGHT_MARGIN']
                    ) {
                        $resultSections[] = $possibleParent['ID'];
                    }
                }
            }
        }
        return array_unique($resultSections);
    }

    protected function filterMenuBySectionsIds($arMenu, $sectionsIds): array
    {
        foreach ($arMenu as $sectionId => $arSection) {
            if (in_array($sectionId, $sectionsIds)) {
                self::setIsShowFlag($arMenu, $sectionId, $sectionsIds);
            }
        }
        return array_filter($arMenu, function ($section) {
            return $section['IS_SHOW'];
        });
    }

    /**
     * Проставляет ключ IS_SHOW в пунктах меню, которые нужно отображать
     *
     * @param array $arMenu Полный массив пунктов меню
     * @param int $sectionId Идентификатор раздела, у которого надо проставить ключ
     * @param array $sectionsIds Массив id разделов, в которых есть подходящие товары
     */
    private static function setIsShowFlag(&$arMenu, $sectionId, $sectionsIds)
    {
        if ($sectionId && $arMenu[$sectionId]) {
            $arMenu[$sectionId]['IS_SHOW'] = 1;
            self::setIsShowFlag($arMenu, $arMenu[$sectionId]['IBLOCK_SECTION_ID'], $sectionsIds);
        }
    }

    public function handleMenu(array $menu)
    {
        foreach ($menu as &$item) {
            $item['SECTION_PAGE_URL'] = \CIBlock::ReplaceSectionUrl($item['SECTION_TEMPLATE'], [
                'SECTION_CODE' => $item['CODE'],
                'IBLOCK_SECTION_ID' => $item['ID']
            ], false, 'E');
        }
        $menu = collect($menu)->sortBy('SORT')->groupBy('UF_MENU_COLUMN')->toArray();
        ksort($menu);
        return $menu;
    }
}
