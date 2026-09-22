<?php

use Beeralex\Core\Repository\IblockRepository;
use Beeralex\Core\Repository\IblockSectionRepository;
use Beeralex\Core\Service\UrlService;
use Bitrix\Main\Application;
use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class BeeralexMenu extends CBitrixComponent
{
    protected array $defaultSelect = [
        'ID',
        'NAME',
        'CODE',
        'IBLOCK_SECTION_ID',
        'SECTION_PAGE_URL' => 'IBLOCK.SECTION_PAGE_URL',
    ];

    protected IblockRepository $iblockRepository;
    protected IblockSectionRepository $iblockSectionRepository;

    public function onPrepareComponentParams($params)
    {
        $params['IBLOCK_ID'] = (int)($params['IBLOCK_ID'] ?? 0);

        if (!empty($params['SELECT']) && is_array($params['SELECT'])) {
            $this->defaultSelect = array_unique(
                array_merge($this->defaultSelect, $params['SELECT'])
            );
        }

        $this->iblockRepository = new IblockRepository($params['IBLOCK_ID']);
        $this->iblockSectionRepository = $this->iblockRepository->getIblockSectionRepository();

        return $params;
    }

    public function executeComponent()
    {
        if (!$this->arParams['IBLOCK_ID']) {
            return [];
        }

        if ($this->startResultCache(false, $this->getCacheId())) {

            Loader::requireModule('iblock');

            $taggedCache = Application::getInstance()->getTaggedCache();
            $taggedCache->startTagCache($this->getCachePath());

            $taggedCache->registerTag('iblock_id_' . $this->arParams['IBLOCK_ID']);

            $this->arResult['MENU'] = $this->getMenu();

            $taggedCache->endTagCache();

            $this->includeComponentTemplate();
        }

        return $this->arResult['MENU'];
    }

    protected function getMenu(): array
    {
        return $this->buildMenuFromIblockSections($this->arParams['IBLOCK_ID']);
    }

    protected function buildMenuFromIblockSections(int $iblockId): array
    {
        $sections = $this->iblockSectionRepository->getList([
            'select' => $this->defaultSelect,
            'filter' => [
                'IBLOCK_ID' => $iblockId,
                'ACTIVE' => 'Y',
            ],
            'order' => ['LEFT_MARGIN' => 'ASC'],
        ])->fetchAll();

        if (!$sections) {
            return [];
        }

        $sectionIdsWithActiveElements = array_flip(
            $this->getSectionIdsWithActiveElements($iblockId)
        );

        $taggedCache = Application::getInstance()->getTaggedCache();

        $tree = [];
        $byId = [];

        foreach ($sections as $section) {
            $taggedCache->registerTag('iblock_section_' . $section['ID']);

            $section['LINK'] = service(UrlService::class)->getSectionUrl(
                ['CODE' => $section['CODE'], 'ID' => $section['ID']],
                $section['SECTION_PAGE_URL'],
                false,
                'S'
            )['clean_url'];

            $section['CHILDREN'] = [];
            $section['HAS_ACTIVE_ELEMENTS'] = isset($sectionIdsWithActiveElements[$section['ID']]);

            $byId[$section['ID']] = $section;
        }

        foreach ($byId as &$section) {
            if (
                $section['IBLOCK_SECTION_ID']
                && isset($byId[$section['IBLOCK_SECTION_ID']])
            ) {
                $byId[$section['IBLOCK_SECTION_ID']]['CHILDREN'][] = &$section;
            } else {
                $tree[] = &$section;
            }
        }
        unset($section);

        return $this->filterSectionsWithActiveElements($tree);
    }

    /**
     * ID разделов, в которых есть хотя бы один активный элемент.
     */
    protected function getSectionIdsWithActiveElements(int $iblockId): array
    {
        $rows = $this->iblockRepository->getList([
            'select' => ['IBLOCK_SECTION_ID'],
            'filter' => [
                'IBLOCK_ID' => $iblockId,
                'ACTIVE' => 'Y',
            ],
            'group' => ['IBLOCK_SECTION_ID'],
        ])->fetchAll();

        return array_column($rows, 'IBLOCK_SECTION_ID');
    }

    /**
     * Оставляет только разделы, у которых есть свои активные элементы,
     * либо остались дочерние разделы после рекурсивной фильтрации.
     */
    protected function filterSectionsWithActiveElements(array $sections): array
    {
        $result = [];

        foreach ($sections as $section) {
            $section['CHILDREN'] = $this->filterSectionsWithActiveElements($section['CHILDREN']);

            if ($section['HAS_ACTIVE_ELEMENTS'] || $section['CHILDREN']) {
                $result[] = $section;
            }
        }

        return $result;
    }

    public function getIblockRepository(): IblockRepository
    {
        return $this->iblockRepository;
    }

    public function getIblockSectionRepository(): IblockSectionRepository
    {
        return $this->iblockSectionRepository;
    }
}