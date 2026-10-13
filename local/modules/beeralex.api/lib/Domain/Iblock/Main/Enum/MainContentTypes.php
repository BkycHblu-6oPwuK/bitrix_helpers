<?php

namespace Beeralex\Api\Domain\Iblock\Main\Enum;

enum MainContentTypes : string
{
    case PRODUCTS_SLIDER = 'slider_products';
    case NEW = 'new';
    case POPULAR = 'popular';
    case ARTICLES = 'slider_articles';
    case MAIN_BANNER = 'main_banner';
}