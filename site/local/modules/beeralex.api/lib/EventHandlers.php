<?php

namespace Beeralex\Api;

use Beeralex\Api\Domain\EventHandlers\CouponHandler;
use Beeralex\Api\Domain\EventHandlers\FUserHandler;
use Beeralex\Api\Domain\EventHandlers\JwtTokenHandler;
use Bitrix\Main\Context;

class EventHandlers
{
    public static function onPageStart()
    {
        $options = service(Options::class);
        if(!$options->spaApiEnabled) {
            return;
        }
        
        $request = Context::getCurrent()->getRequest();

        JwtTokenHandler::handle($request); // авторизация по JWT токену
        CouponHandler::handle($request); // обработка купона из заголовка
    }
}
