<?php
namespace Itb\Catalog\Types\Enum;

use Itb\User\Enum\Gender;

enum TypesCatalog: string
{
    case MAN = Gender::MAN->value;
    case WOMAN = Gender::WOMAN->value;
    case ALL = 'all';
}