<?php

use Beeralex\Core\Repository\IblockRepository;
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

    protected $iblockSectionRepository;

    public function onPrepareComponentParams($params)
    {
        $params['IBLOCK_ID'] = (int)($params['IBLOCK_ID'] ?? 0);

        if (!empty($params['SELECT']) && is_array($params['SELECT'])) {
            $this->defaultSelect = array_unique(
                array_merge($this->defaultSelect, $params['SELECT'])
            );
        }

        $iblockRepository = new IblockRepository($params['IBLOCK_ID']);
        $this->iblockSectionRepository = $iblockRepository->getIblockSectionRepository();

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

        return $tree;
    }
}
