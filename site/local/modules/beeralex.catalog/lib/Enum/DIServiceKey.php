<?php

namespace Beeralex\Catalog\Enum;

enum DIServiceKey: string
{
    case PRODUCT_REPOSITORY = 'beeralex.catalog.product.repository';
    case OFFERS_REPOSITORY = 'beeralex.catalog.offer.repository';
    case EMPTY_OFFERS_REPOSITORY = 'beeralex.catalog.empty.offer.repository';
    case SORTING_REPOSITORY = 'beeralex.catalog.sorting.repository';
    case SORTING_SERVICE = 'beeralex.catalog.sorting.service';
}