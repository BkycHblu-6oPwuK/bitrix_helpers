<?php

namespace App\Main\Enum;

enum DIServiceKey: string
{
    case CATALOG_REPOSITORY = 'catalogRepository';
    case OFFERS_REPOSITORY = 'offersRepository';
    case EMPTY_OFFERS_REPOSITORY = 'emptyOffersRepository';
}