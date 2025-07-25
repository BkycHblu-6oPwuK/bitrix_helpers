<?php

namespace Itb\Catalog;

use Bitrix\Main\Loader;
use Bitrix\Sale\Internals\PersonTypeTable;

class PersonType
{
    public static function getIndividualPersonId(): int
    {
        static $personId = null;
        if ($personId === null) {
            Loader::includeModule('sale');
            $personId = PersonTypeTable::query()->setSelect(['ID'])->where('LID', \Bitrix\Main\Context::getCurrent()->getSite())->where('NAME', 'Физическое лицо')->setCacheTtl(86400)->fetch()['ID'];
        }
        return $personId;
    }

    public static function getLegalPersonId(): int
    {
        static $personId = null;
        if ($personId === null) {
            Loader::includeModule('sale');
            $personId = PersonTypeTable::query()->setSelect(['ID'])->where('LID', \Bitrix\Main\Context::getCurrent()->getSite())->where('NAME', 'Юридическое лицо')->setCacheTtl(86400)->fetch()['ID'];
        }
        return $personId;
    }
}
