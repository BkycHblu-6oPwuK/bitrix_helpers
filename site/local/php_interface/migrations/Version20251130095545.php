<?php

namespace Sprint\Migration;

use Beeralex\User\Auth\Session\UserSessionTable;
use Bitrix\Main\Loader;

class Version20251130095545 extends Version
{
    protected $author = "admin";

    protected $description = "таблица сессий пользователя";

    protected $moduleVersion = "5.4.1";

    /**
     * @throws Exceptions\HelperException
     * @return bool|void
     */
    public function up()
    {
      Loader::includeModule('beeralex.user');
      UserSessionTable::createTable();
    }
}
