<?php

use Itb\Main\PageHelper;
use Itb\User\User;

class ItbAuth extends CBitrixComponent
{
    /**
     * @inheritDoc
     */
    public function executeComponent()
    {
        $this->arResult['isAuth'] = User::current()->isAuthorized();
        $this->arResult['profilePageUrl'] = PageHelper::getProfilePageUrl();
        $this->includeComponentTemplate();
    }
}
