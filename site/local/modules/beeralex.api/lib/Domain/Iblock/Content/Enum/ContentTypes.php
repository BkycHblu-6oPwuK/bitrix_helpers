<?php

namespace Beeralex\Api\Domain\Iblock\Content\Enum;

enum ContentTypes : string
{
    case SLIDER = 'slider';
    case PRODUCTS_NEW = 'new';
    case PRODUCTS_POPULAR = 'popular';
    case VIDEO = 'video';
    case ARTICLES = 'slider_articles';
    case MAIN_BANNER = 'main_banner';
    case FORM = 'form';
}