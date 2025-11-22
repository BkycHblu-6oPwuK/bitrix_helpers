<?php

use Beeralex\Api\UrlService;
use Beeralex\Core\Model\SectionModel;
use Beeralex\Core\Service\IblockService;

class BeeralexCatalogSectionList extends \CBitrixComponent
{
    /**
     * @var \Bitrix\Iblock\ORM\CommonElementTable|string
     */
    protected $sectionEntity;

    public function onPrepareComponentParams($params)
    {
        $catalogId = service(IblockService::class)->getIblockIdByCode('catalog');
        $this->sectionEntity = SectionModel::compileEntityByIblock($catalogId);

        $params['SMART_FILTER_NAME'] = $params['SMART_FILTER_NAME'] ?: 'arrFilter';
        $params['SECTION_ID'] = (int)($params['SECTION_ID'] ?? 0);
        $params['DEPTH_LEVEL'] = (int)($params['DEPTH_LEVEL'] ?? 1);

        return $params;
    }

    public function executeComponent()
    {
        $filter = $this->getFilter();

        if (empty($filter) && !$this->arParams['SECTION_ID']) {
            if ($this->startResultCache(false, 'catalog_section_list_all', 'beeralex/catalog.section.list')) {
                $taggedCache = \Bitrix\Main\Application::getInstance()->getTaggedCache();
                $taggedCache->startTagCache('beeralex/catalog.section.list');
                try {
                    $taggedCache->registerTag('iblock_id_' . service(IblockService::class)->getIblockIdByCode('catalog'));
                    $this->arResult['sections'] = $this->getRootSections();
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

        if ($this->startResultCache(false, [$this->arParams['SECTION_ID'], $filter], 'beeralex/catalog.section.list')) {
            $taggedCache = \Bitrix\Main\Application::getInstance()->getTaggedCache();
            $taggedCache->startTagCache('beeralex/catalog.section.list');
            try {
                $taggedCache->registerTag('iblock_id_' . service(IblockService::class)->getIblockIdByCode('catalog'));
                $this->arResult['sections'] = $this->getSections();
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

    protected function getRootSections(): array
    {
        $res = $this->sectionEntity::query()
            ->setSelect(['ID', 'CODE', 'NAME', 'PICTURE', 'UF_CUSTOM_NAME', 'SECTION_TEMPLATE' => 'iblock.SECTION_PAGE_URL'])
            ->where('ACTIVE', 'Y')
            ->where('GLOBAL_ACTIVE', 'Y')
            ->whereNull('IBLOCK_SECTION_ID')
            ->setOrder(['SORT' => 'ASC'])
            ->exec();

        $sections = [];
        while ($item = $res->fetch()) {
            $this->replaceUrl($item);
            if ($item['PICTURE']) {
                $item['PICTURE'] = \CFile::GetPath($item['PICTURE']);
            }
            if ($item['UF_CUSTOM_NAME']) {
                $item['NAME'] = $item['UF_CUSTOM_NAME'];
            }
            $sections[] = [
                'id' => (int)$item['ID'],
                'name' => $item['NAME'],
                'code' => $item['CODE'],
                'url' => $item['URL'],
                'picture' => $item['PICTURE'],
            ];
        }

        return $sections;
    }

    protected function getSections()
    {
        $sections = [];
        $sectionIds = $this->getSectionIds();

        if (!empty($sectionIds)) {
            $sectionsRes = $this->sectionEntity::query()
                ->setSelect([
                    'ID',
                    'CODE',
                    'NAME',
                    'UF_CUSTOM_NAME',
                    'IBLOCK_SECTION_ID',
                    'SECTION_TEMPLATE' => 'iblock.SECTION_PAGE_URL',
                    'PICTURE'
                ])
                ->whereNotNull('PICTURE')
                ->where('GLOBAL_ACTIVE', 'Y')
                ->where('ACTIVE', 'Y')
                ->whereIn('ID', $sectionIds)
                ->setOrder(['LEFT_MARGIN' => 'ASC'])
                ->exec();
            while ($item = $sectionsRes->fetch()) {
                $this->replaceUrl($item);
                if ($item['PICTURE']) {
                    $item['PICTURE'] = \CFile::GetPath($item['PICTURE']);
                }
                if ($item['UF_CUSTOM_NAME']) {
                    $item['NAME'] = $item['UF_CUSTOM_NAME'];
                }
                $sections[] = [
                    'id' => (int)$item['ID'],
                    'parent_section_id' => (int)$item['IBLOCK_SECTION_ID'],
                    'name' => $item['NAME'],
                    'code' => $item['CODE'],
                    'url' => $item['URL'],
                    'picture' => $item['PICTURE'],
                ];
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
        $baseDepth = null;
        $sections = $this->getSectionsMap($sectionId);
        if (isset($sections[$sectionId])) {
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

                    if ($depthLevel !== null && $baseDepth !== null) {
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

    protected function getSectionsMap(int $sectionId): array
    {
        $sectionMap = [];

        $base = $this->sectionEntity::query()
            ->setSelect(['LEFT_MARGIN', 'RIGHT_MARGIN'])
            ->where('ID', $sectionId)
            ->setCacheTtl(86400)
            ->exec()
            ->fetch();

        if (!$base) {
            return [];
        }

        $left = (int)$base['LEFT_MARGIN'];
        $right = (int)$base['RIGHT_MARGIN'];

        $result = $this->sectionEntity::query()
            ->setSelect(['ID', 'LEFT_MARGIN', 'RIGHT_MARGIN', 'DEPTH_LEVEL'])
            ->where('LEFT_MARGIN', '>=', $left)
            ->where('RIGHT_MARGIN', '<=', $right)
            ->setCacheTtl(86400)
            ->exec();

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

    protected function getProductSectionIds(int $sectionId): array
    {
        $filter = array_merge(
            [
                'IBLOCK_ID' => service(IblockService::class)->getIblockIdByCode('catalog'),
                'ACTIVE' => 'Y',
                '=AVAILABLE' => 'Y',
                'SECTION_ID' => $sectionId,
                'INCLUDE_SUBSECTIONS' => 'Y',
            ],
            $this->getFilter()
        );

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
            'SECTION_CODE' => $item['CODE'],
            'IBLOCK_SECTION_ID' => $item['ID']
        ], $item['SECTION_TEMPLATE']);
        $item['URL'] = $url;
    }
}
