<?php

use Beeralex\Core\Repository\IblockRepository;
use Beeralex\Core\Repository\IblockSectionRepository;
use Beeralex\Core\Service\UrlService;
use Bitrix\Main\Loader;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

class BeeralexMenu extends CBitrixComponent
{
    protected array $defaultSelect = ['ID', 'NAME', 'CODE', 'IBLOCK_SECTION_ID', 'SECTION_PAGE_URL' => 'IBLOCK.SECTION_PAGE_URL'];
    protected IblockSectionRepository $iblockSectionRepository;

    public function onPrepareComponentParams($params)
    {
        $params['IBLOCK_ID'] = (int)($params['IBLOCK_ID'] ?? 0);
        if ($params['SELECT'] && is_array($params['SELECT'])) {
            $this->defaultSelect = array_unique(array_merge($this->defaultSelect, $params['SELECT']));
        }
        $iblockRepository = new IblockRepository((int)$params['IBLOCK_ID']);
        $this->iblockSectionRepository = $iblockRepository->getIblockSectionRepository();
        return $params;
    }
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
        $iblockId = $this->arParams['IBLOCK_ID'];
        if (!$iblockId) {
            return [];
        }

        return $this->buildMenuFromIblockSections($iblockId);
    }

    protected function buildMenuFromIblockSections(int $iblockId): array
    {
        $sections = $this->iblockSectionRepository->getList([
            'select' => $this->defaultSelect,
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
