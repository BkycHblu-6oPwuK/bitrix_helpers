<?php

namespace Itb\Main\Enum;

enum ContentTypes : string
{
    case SLIDER = 'slider';
    case PRODUCTS_NEW = 'new';
    case PRODUCTS_POPULAR = 'popular';
    case VIDEO = 'video';
    case TWO_ARTICLES = 'two_articles';
    case VKONTAKTE = 'vkontakte';
    case ARTICLES = 'slider_articles';
    case CATALOG_RAZDEL = 'catalog_razdel';
    case MAIN_BANNER = 'main_banner';
}