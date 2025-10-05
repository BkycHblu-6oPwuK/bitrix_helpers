<?php

namespace App\Catalog\Type;

use App\Catalog\Type\Enum\TypesCatalog;

class CatalogSwitcher implements Contracts\CatalogSwitcherContract
{
    private const COOKIE_NAME = 'TYPES_CATALOG';
    private TypesCatalog $current;

    public function __construct()
    {
        $this->current = $this->detect();
    }

    /**
     * Определение текущего типа каталога
     */
    private function detect(): TypesCatalog
    {
        $value = $_COOKIE[self::COOKIE_NAME] ?? TypesCatalog::ALL->value;
        return TypesCatalog::tryFrom($value) ?? TypesCatalog::ALL;
    }

    /**
     * Получить текущий тип
     */
    public function get(): TypesCatalog
    {
        return $this->current;
    }

    /**
     * Установить новый тип и сохранить в cookie
     */
    public function set(TypesCatalog $type): void
    {
        setcookie(self::COOKIE_NAME, $type->value, time() + 60 * 60 * 24 * 30, '/');
        $_COOKIE[self::COOKIE_NAME] = $type->value; // чтобы работало сразу
        $this->current = $type;
    }

    /**
     * Проверки
     */
    public function isMan(): bool
    {
        return $this->current === TypesCatalog::MAN;
    }

    public function isWoman(): bool
    {
        return $this->current === TypesCatalog::WOMAN;
    }

    public function isAll(): bool
    {
        return $this->current === TypesCatalog::ALL;
    }
}
