<?php

use Beeralex\Core\Service\UrlService;
use Beeralex\Core\Repository\IblockRepository;
use Beeralex\Core\Repository\IblockSectionRepository;
use Beeralex\Core\Service\FileService;
use Beeralex\Core\Service\IblockService;

class BeeralexCatalogSectionList extends \CBitrixComponent
{
    protected IblockRepository $iblockRepository;
    protected IblockSectionRepository $iblockSectionRepository;
    protected FileService $fileService;

    protected array $defaultSelect = [
        'ID',
        'NAME',
        'CODE',
        'PICTURE',
        'SECTION_TEMPLATE' => 'IBLOCK.SECTION_PAGE_URL',
        'LEFT_MARGIN',
        'RIGHT_MARGIN',
        'DEPTH_LEVEL',
    ];

    public function onPrepareComponentParams($params)
    {
        if (!$params['IBLOCK_ID']) {
            $params['IBLOCK_ID'] = service(IblockService::class)->getIblockIdByCode('catalog');
        }

        if ($params['SELECT'] && is_array($params['SELECT'])) {
            $this->defaultSelect = array_unique(array_merge($this->defaultSelect, $params['SELECT']));
        }
        $this->iblockRepository = new IblockRepository((int)$params['IBLOCK_ID']);
        $this->iblockSectionRepository = $this->iblockRepository->getIblockSectionRepository();
        $this->fileService = service(FileService::class);
        $params['IS_CATALOG_IBLOCK'] = (bool)($params['IS_CATALOG_IBLOCK'] ?? true);
        $params['SMART_FILTER_NAME'] = $params['SMART_FILTER_NAME'] ?: 'arrFilter';
        $params['SECTION_ID'] = (int)($params['SECTION_ID'] ?? 0);
        $params['DEPTH_LEVEL'] = (int)($params['DEPTH_LEVEL'] ?? 1);

        return $params;
    }

    public function executeComponent()
    {
        $filter = $this->getFilter();
        $isRootRequest = empty($filter) && !$this->arParams['SECTION_ID'];

        $cacheId = $isRootRequest
            ? 'catalog_section_list_all'
            : [$this->arParams['SECTION_ID'], $filter];
        $dir = '/' . SITE_ID . '/beeralex/catalog.section.list';
        if ($this->startResultCache(false, $cacheId, $dir)) {
            $taggedCache = \Bitrix\Main\Application::getInstance()->getTaggedCache();
            $taggedCache->startTagCache($dir);
            try {
                $taggedCache->registerTag('iblock_id_' . $this->arParams['IBLOCK_ID']);
                $this->arResult['SECTIONS'] = $this->getSections();
                $this->includeComponentTemplate();
            } catch (\Throwable $e) {
                $this->abortResultCache();
                throw $e;
            } finally {
                $taggedCache->endTagCache();
            }
        }

        return $this->arResult;
    }

    public function getFilter(): array
    {
        return $GLOBALS[$this->arParams['SMART_FILTER_NAME']] ?? [];
    }

    /**
     * Работает как для конкретного SECTION_ID, так и для "корня" (SECTION_ID = 0):
     * в обоих случаях возвращает только разделы, в которых (с учётом DEPTH_LEVEL)
     * реально есть активные и доступные товары.
     */
    protected function getSections(): array
    {
        $sections = [];
        $sectionIds = $this->getSectionIds();

        if (!empty($sectionIds)) {
            $sections = $this->fileService->addPictireSrcInQuery($this->iblockSectionRepository->query(), 'PICTURE')
                ->setSelect(array_merge($this->defaultSelect, ['PICTURE_SRC']))
                ->where('GLOBAL_ACTIVE', 'Y')
                ->where('ACTIVE', 'Y')
                ->whereIn('ID', $sectionIds)
                ->setOrder(['LEFT_MARGIN' => 'ASC'])
                ->exec()->fetchAll();

            foreach ($sections as &$item) {
                $item['SELECTED'] = $this->arParams['SELECTED_SECTION_ID'] == $item['ID'];
                $this->replaceUrl($item);
            }
        }

        return $sections;
    }

    protected function getSectionIds(): array
    {
        $sectionId = $this->arParams['SECTION_ID'];
        $depthLevel = $this->arParams['DEPTH_LEVEL'];
        $excludedLeft = null;
        $excludedRight = null;
        $baseDepth = 0; // виртуальный корень имеет глубину 0

        $sections = $this->getSectionsMap($sectionId);

        if ($sectionId > 0 && isset($sections[$sectionId])) {
            $excludedLeft = $sections[$sectionId]['LEFT'];
            $excludedRight = $sections[$sectionId]['RIGHT'];
            $baseDepth = $sections[$sectionId]['DEPTH_LEVEL'];
        }

        $resultSectionIds = [];

        foreach ($this->getProductSectionIds($sectionId) as $secId) {
            if (!isset($sections[$secId])) {
                continue;
            }

            $left = $sections[$secId]['LEFT'];
            $right = $sections[$secId]['RIGHT'];

            foreach ($sections as $candidate) {
                if ($candidate['LEFT'] <= $left && $candidate['RIGHT'] >= $right) {
                    if (
                        $excludedLeft !== null &&
                        $excludedRight !== null &&
                        $candidate['LEFT'] <= $excludedLeft &&
                        $candidate['RIGHT'] >= $excludedRight
                    ) {
                        continue;
                    }

                    if ($depthLevel !== null) {
                        $relativeDepth = $candidate['DEPTH_LEVEL'] - $baseDepth;
                        if ($relativeDepth > $depthLevel || $relativeDepth <= 0) {
                            continue;
                        }
                    }
                    $resultSectionIds[] = $candidate['ID'];
                }
            }
        }

        return array_values(array_unique($resultSectionIds));
    }

    /**
     * @param int $sectionId 0 означает "весь инфоблок" (виртуальный корень).
     */
    protected function getSectionsMap(int $sectionId): array
    {
        $sectionMap = [];
        $query = $this->iblockSectionRepository->query()
            ->setSelect(['ID', 'LEFT_MARGIN', 'RIGHT_MARGIN', 'DEPTH_LEVEL'])
            ->setCacheTtl(86400);

        if ($sectionId > 0) {
            $base = $this->iblockSectionRepository->query()
                ->setSelect(['LEFT_MARGIN', 'RIGHT_MARGIN'])
                ->where('ID', $sectionId)
                ->setCacheTtl(86400)
                ->exec()
                ->fetch();

            if (!$base) {
                return [];
            }

            $query
                ->where('LEFT_MARGIN', '>=', (int)$base['LEFT_MARGIN'])
                ->where('RIGHT_MARGIN', '<=', (int)$base['RIGHT_MARGIN']);
        }

        $result = $query->exec();

        while ($row = $result->fetch()) {
            $sectionMap[$row['ID']] = [
                'ID' => (int)$row['ID'],
                'LEFT' => (int)$row['LEFT_MARGIN'],
                'RIGHT' => (int)$row['RIGHT_MARGIN'],
                'DEPTH_LEVEL' => (int)$row['DEPTH_LEVEL'],
            ];
        }

        return $sectionMap;
    }

    /**
     * @param int $sectionId 0 означает поиск товаров по всему инфоблоку,
     *                       без ограничения по конкретному разделу.
     */
    protected function getProductSectionIds(int $sectionId): array
    {
        $filter = [
            'IBLOCK_ID' => $this->arParams['IBLOCK_ID'],
            'ACTIVE' => 'Y'
        ];

        if ($this->arParams['IS_CATALOG_IBLOCK'] === true) {
            $filter['=AVAILABLE'] = 'Y';
        }

        if ($sectionId > 0) {
            $filter['SECTION_ID'] = $sectionId;
            $filter['INCLUDE_SUBSECTIONS'] = 'Y';
        }

        $filter = array_merge($filter, $this->getFilter());

        $productSectionIds = [];
        $res = \CIBlockElement::GetList([], $filter, false, false, ['ID', 'IBLOCK_SECTION_ID']);
        while ($item = $res->Fetch()) {
            $productSectionIds[] = (int)$item['IBLOCK_SECTION_ID'];
        }
        return array_unique($productSectionIds);
    }

    protected function replaceUrl(&$item): void
    {
        $url = service(UrlService::class)->getSectionUrl([
            'CODE' => $item['CODE'],
            'ID' => $item['ID']
        ], $item['SECTION_TEMPLATE'],);
        $item['URL'] = $url['clean_url'];
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
