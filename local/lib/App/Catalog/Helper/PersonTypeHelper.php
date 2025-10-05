<?php
namespace App\Catalog\Helper;

use Bitrix\Main\Loader;
use Bitrix\Sale\Internals\PersonTypeTable;

Loader::includeModule('sale');

class PersonTypeHelper
{
    public static function getIndividualPersonId(): int
    {
        static $personId = null;
        if ($personId === null) {
            $personId = PersonTypeTable::query()->setSelect(['ID'])->where('LID', \Bitrix\Main\Context::getCurrent()->getSite())->where('NAME', 'Физическое лицо')->setCacheTtl(86400)->fetch()['ID'];
        }
        return $personId;
    }

    public static function getLegalPersonId(): int
    {
        static $personId = null;
        if ($personId === null) {
            $personId = PersonTypeTable::query()->setSelect(['ID'])->where('LID', \Bitrix\Main\Context::getCurrent()->getSite())->where('NAME', 'Юридическое лицо')->setCacheTtl(86400)->fetch()['ID'];
        }
        return $personId;
    }
}
