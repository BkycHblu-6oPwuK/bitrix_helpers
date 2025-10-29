<?php
namespace App\Catalog\Type\Contracts;

interface CatalogContextContract
{
    /**
     * Вернёт символьный код раздела верхнего уровня
     * или пустую строку если выбран ALL
     */
    public function getRootSectionCode(): string;

    /**
     * Вернёт список разделов верхнего уровня
     * с учётом текущего выбранного типа
     */
    public function getRootSections(): array;
}
