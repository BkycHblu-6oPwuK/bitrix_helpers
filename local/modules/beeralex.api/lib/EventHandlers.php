<?php

namespace Beeralex\Api;

use Beeralex\Api\Domain\EventHandlers\CouponHandler;
use Beeralex\Api\Domain\EventHandlers\JwtTokenHandler;
use Bitrix\Main\Application;

class EventHandlers
{
    public static function onPageStart()
    {
        $options = service(Options::class);
        if (!$options->spaApiEnabled) {
            return;
        }

        $app = Application::getInstance();
        $request = $app->getContext()->getRequest();

        JwtTokenHandler::handle($request); // авторизация по JWT токену
        CouponHandler::handle($request); // обработка купона из заголовка
    }
}
