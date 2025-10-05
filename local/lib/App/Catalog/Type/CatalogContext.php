<?php

namespace App\Catalog\Type;

use App\Catalog\Type\Enum\TypesCatalog;
use Itb\Core\Helpers\FilesHelper;

class CatalogContext implements Contracts\CatalogContextContract
{
    private CatalogSwitcher $switcher;
    /**
     * @var \Bitrix\Iblock\SectionTable|string
     */
    private $sectionEntity;

    /**
     * @param CatalogSwitcher $switcher
     * @param \Bitrix\Iblock\SectionTable|string $sectionEntity Класс сущности разделов инфоблока катал
     */
    public function __construct(CatalogSwitcher $switcher, string $sectionEntity)
    {
        $this->switcher = $switcher;
        $this->sectionEntity = $sectionEntity;
    }

    /**
     * Символьный код раздела верхнего уровня для текущего выбранного типа.
     * Если выбран ALL → вернёт пустую строку.
     */
    public function getRootSectionCode(?TypesCatalog $type = null): string
    {
        $type = $type ?? $this->switcher->get();

        if ($type === TypesCatalog::ALL) {
            return '';
        }

        $section = $this->sectionEntity::query()
            ->setSelect(['CODE'])
            ->whereNotNull('UF_TYPE_CATALOG')
            ->setFilter([
                '=DEPTH_LEVEL' => 1,
                '=ACTIVE' => 'Y',
                '=UF_TYPE_CATALOG_ENUM.XML_ID' => $type->value,
            ])
            ->setLimit(1)
            ->setCacheTtl(86400)
            ->cacheJoins(true)
            ->fetch();

        return $section['CODE'] ?? '';
    }

    /**
     * Получить массив всех разделов верхнего уровня для текущего типа.
     * Если ALL → вернёт все активные разделы с заполненным перечислением
     */
    public function getRootSections(?TypesCatalog $type = null): array
    {
        $type = $type ?? $this->switcher->get();

        $filter = [
            '=DEPTH_LEVEL' => 1,
            '=ACTIVE' => 'Y',
        ];

        if ($type !== TypesCatalog::ALL) {
            $filter['=UF_TYPE_CATALOG_ENUM.XML_ID'] = $type->value;
        }

        return FilesHelper::addPictireSrcInQuery($this->sectionEntity::query(), 'UF_INDEX_IMAGE')
            ->setSelect(['ID', 'NAME', 'CODE', 'TYPE' => 'UF_TYPE_CATALOG_ENUM.XML_ID', 'UF_CUSTOM_NAME', 'PICTURE_SRC'])
            ->whereNotNull('UF_TYPE_CATALOG')
            ->setFilter($filter)
            ->setCacheTtl(86400)
            ->cacheJoins(true)
            ->setOrder(['SORT' => 'ASC'])
            ->fetchAll();
    }
}
