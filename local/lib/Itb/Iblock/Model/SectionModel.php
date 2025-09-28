<?php

namespace Itb\Iblock\Model;

use Bitrix\Main\Loader;
use Bitrix\Main\UserFieldTable;
use Bitrix\Iblock\IblockTable;

Loader::includeModule('iblock');

/**
 * Модель раздела инфоблока с поддержкой пользовательских полей, в том числе ENUM (перечисление)
 */
class SectionModel
{
    private static $entityInstance = [];

    /**
     * @param int|string|Iblock $iblock Iblock object, or API_CODE, or ID
     *
     * @return SectionTable|string|null
     */
    public static function compileEntityByIblock($iblock)
    {
        $iblockId = static::resolveIblockId($iblock);

        if ($iblockId <= 0) {
            return null;
        }

        if (!isset(self::$entityInstance[$iblockId])) {
            $className = 'Section' . $iblockId . 'Table';
            $entityName = "\\Itb\\Iblock\\" . $className;
            $referenceName = 'Bitrix\Iblock\Section' . $iblockId;

            $ufId = "IBLOCK_{$iblockId}_SECTION";
            $ufEnums = UserFieldTable::getList([
                'filter' => ['ENTITY_ID' => $ufId, 'USER_TYPE_ID' => 'enumeration'],
                'select' => ['FIELD_NAME'],
            ])->fetchAll();

            $enumFieldsCode = '';
            foreach ($ufEnums as $uf) {
                $fieldName = $uf['FIELD_NAME'];
                $enumFieldsCode .= '
                    $fields["' . $fieldName . '_ENUM"] = [
                        "data_type" => "\\Itb\\Iblock\\Model\\UserFieldEnumTable",
                        "reference" => ["=this.' . $fieldName . '" => "ref.ID"],
                    ];
                ';
            }

            $entity = '
            namespace Itb\Iblock;
            class ' . $className . ' extends \Bitrix\Iblock\SectionTable
            {
                public static function getUfId()
                {
                    return "IBLOCK_' . $iblockId . '_SECTION";
                }
                
                public static function getMap(): array
                {
                    $fields = parent::getMap();
                    $fields["PARENT_SECTION"] = [
                        "data_type" => "' . $referenceName . '",
                        "reference" => ["=this.IBLOCK_SECTION_ID" => "ref.ID"],
                    ];
                    ' . $enumFieldsCode . '
                    return $fields;
                }
                
                public static function setDefaultScope($query)
                {
                    return $query->where("IBLOCK_ID", ' . $iblockId . ');
                }
            }';
            eval($entity);
            self::$entityInstance[$iblockId] = $entityName;
        }

        return self::$entityInstance[$iblockId];
    }

    /**
     * @param int|string|Iblock $iblock
     *
     * @return int|null
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\ObjectPropertyException
     * @throws \Bitrix\Main\SystemException
     */
    protected static function resolveIblockId($iblock): ?int
    {
        $iblockId = null;

        if ($iblock instanceof Iblock) {
            $iblockId = $iblock->getId();
        } elseif (is_string($iblock)) {
            $row = IblockTable::query()
                ->addSelect('ID')
                ->where('API_CODE', $iblock)
                ->fetch();

            if (!empty($row)) {
                $iblockId = (int)$row['ID'];
            }
        }

        if (empty($iblockId) && is_numeric($iblock)) {
            $iblockId = (int)$iblock;
        }

        return $iblockId;
    }
}
