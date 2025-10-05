<?php
namespace App\Catalog\Type\Enum;

use App\User\Enum\Gender;

enum TypesCatalog: string
{
    case MAN = Gender::MAN->value;
    case WOMAN = Gender::WOMAN->value;
    case ALL = 'all';
}