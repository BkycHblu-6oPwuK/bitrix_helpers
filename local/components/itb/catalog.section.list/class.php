<?php

use App\Catalog\Helper\CatalogHelper;
use App\Iblock\Model\SectionModel;
use Itb\Core\Helpers\IblockHelper;

class ItbCatalogSectionList extends \CBitrixComponent
{
    /**
     * @var \Bitrix\Iblock\ORM\CommonElementTable|string
     */
    protected $sectionEntity;

    public function onPrepareComponentParams($params)
    {
        $catalogId = IblockHelper::getIblockIdByCode('catalog');
        $this->sectionEntity = SectionModel::compileEntityByIblock($catalogId);
        if (!$params['SMART_FILTER_NAME']) {
            throw new \RuntimeException("not found smart filter name in params");
        }
        if (!$params['SECTION_ID']) {
            throw new \RuntimeException("not found section id in params");
        }
        if (!$params['DEPTH_LEVEL']) {
            $params['DEPTH_LEVEL'] = 1;
        }
        $params['DEPTH_LEVEL'] = (int)$params['DEPTH_LEVEL'];
        $params['SECTION_ID'] = (int)$params['SECTION_ID'];
        return $params;
    }

    public function executeComponent()
    {
        $filter = $GLOBALS[$this->arParams['SMART_FILTER_NAME']];
        if (!empty($filter)) {
            $this->arResult['sections'] = $this->getSections();
            $this->includeComponentTemplate();
        } else {
            if ($this->startResultCache(false, [$this->arParams['SECTION_ID'], $this->arParams['DEPTH_LEVEL']], 'itb/catalog.section.list')) {
                $taggedCache = \Bitrix\Main\Application::getInstance()->getTaggedCache();
                $taggedCache->startTagCache('itb/catalog.section.list');
                try {
                    $taggedCache->registerTag('iblock_id_' . IblockHelper::getIblockIdByCode('catalog'));
                    $this->arResult['sections'] = $this->getSections();
                    $this->includeComponentTemplate();
                } catch (\Throwable $e) {
                    $this->abortResultCache(); // если ошибка — прерываем кеш
                    throw $e;
                } finally {
                    $taggedCache->endTagCache();
                }
            }
        }
        return $this->arResult;
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
                'IBLOCK_ID' => IblockHelper::getIblockIdByCode('catalog'),
                'ACTIVE' => 'Y',
                '=AVAILABLE' => 'Y',
                'SECTION_ID' => $sectionId,
                'INCLUDE_SUBSECTIONS' => 'Y',
            ],
            $GLOBALS[$this->arParams['SMART_FILTER_NAME']] ?? []
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
        $url = \CIBlock::ReplaceSectionUrl($item['SECTION_TEMPLATE'], [
            'SECTION_CODE' => $item['CODE'],
            'IBLOCK_SECTION_ID' => $item['ID']
        ], false, 'E');
        $item['URL'] = $url;
    }
}
