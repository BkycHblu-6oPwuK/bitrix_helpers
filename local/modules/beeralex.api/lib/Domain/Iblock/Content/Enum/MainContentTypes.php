<?php

namespace Beeralex\Api\Domain\Iblock\Content\Enum;

enum MainContentTypes : string
{
    case SLIDER = 'slider';
    case SLIDER_NEW = 'new';
    case PRODUCTS_POPULAR = 'popular';
    case VIDEO = 'video';
    case ARTICLES = 'slider_articles';
    case MAIN_BANNER = 'main_banner';
}