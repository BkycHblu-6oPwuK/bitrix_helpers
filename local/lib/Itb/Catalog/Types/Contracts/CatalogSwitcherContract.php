<?php
namespace Itb\Catalog\Types\Contracts;

use Itb\Catalog\Types\Enum\TypesCatalog;

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
