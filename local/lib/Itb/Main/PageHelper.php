<?php

namespace Itb\Main;

class PageHelper
{
    public static function getCatalogPageUrl(): string
    {
        return '/catalog/';
    }

    public static function getNewProductsPageUrl(): string
    {
        return '/new/';
    }

    public static function getPopularProductsPageUrl(): string
    {
        return '/popular/';
    }

    public static function getProductPageUrl(): string
    {
        return '/product/';
    }

    public static function getProfilePageUrl(): string
    {
        return '/account/';
    }

    public static function getProfileOrdersPageUrl(): string
    {
        return static::getProfilePageUrl() . 'orders/';
    }

    public static function getProfileDressingsPageUrl(): string
    {
        return static::getProfilePageUrl() . 'dressings/';
    }

    public static function getProfileQuestionsPageUrl(): string
    {
        return static::getProfilePageUrl() . 'questions/';
    }

    public static function getCheckoutPageUrl(): string
    {
        return '/checkout/';
    }

    public static function getBasketUrl(): string
    {
        return '/basket/';
    }

    public static function getDressingUrl(): string
    {
        return '/dressing/';
    }
    
    public static function getFavouritesUrl(): string
    {
        return '/favourites/';
    }

    public static function getCurUri(): \Bitrix\Main\Web\Uri
    {
        $server = \Bitrix\Main\Context::getCurrent()->getServer();
        $host = $server->getHttpHost();
        $scheme = $server->getRequestScheme();
        return new \Bitrix\Main\Web\Uri($scheme . '://' . $host . $server->getRequestUri());
    }
}
