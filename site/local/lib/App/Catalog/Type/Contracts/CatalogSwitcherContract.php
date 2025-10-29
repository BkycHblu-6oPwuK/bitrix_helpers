<?php
namespace App\Catalog\Type\Contracts;

use App\Catalog\Type\Enum\TypesCatalog;

interface CatalogSwitcherContract
{
    /**
     * Получить текущий выбранный тип каталога
     */
    public function get(): TypesCatalog;

    /**
     * Установить выбранный тип каталога
     */
    public function set(TypesCatalog $type): void;
}
