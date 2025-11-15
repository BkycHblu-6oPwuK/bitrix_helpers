<?php
declare(strict_types=1);
namespace Beeralex\User;

use Bitrix\Main\ORM\Fields\StringField;

/**
 * таблица пользователей Битрикс с добавленным полем CHECKWORD
 */
class UserTable extends \Bitrix\Main\UserTable
{
    public static function getMap()
    {
        $map = parent::getMap();
        $map[] = (new StringField('CHECKWORD'))->configurePrivate();
        return $map;
    }
}