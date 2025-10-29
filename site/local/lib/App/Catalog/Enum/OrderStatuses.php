<?php

namespace App\Catalog\Enum;

enum OrderStatuses : string
{
    case DRESSING = 'DR';
    case CREATED = 'N';
    case SHIPPED = 'P';
    case TRANSIT = 'D';
    case READY = 'R';
    case SUCCESS = 'F';
    case CANCELED = 'C';
}