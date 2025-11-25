<?php
namespace Beeralex\Catalog\Service;

use Bitrix\Main\Loader;

class PriceService
{
    public function __construct()
    {
        Loader::includeModule('currency');
    }

    /**
     * Приводит цену к виду для отображения на сайте
     *
     * @param float $price
     *
     * @return string
     */
    public function format(?float $price): string
    {
        return \CCurrencyLang::CurrencyFormat($price, 'RUB', false);
    }

    /**
     * Получает процент скидки для цен
     *
     * @param float $oldPrice цена до скидки
     * @param float $newPrice цена со скидкой
     *
     * @return int процент скидки
     */
    public function getSalePercent(?float $oldPrice, ?float $newPrice): int
    {
        if ($oldPrice < $newPrice) {
            return 0;
        }
        if ((float)$oldPrice == 0 || (float)$newPrice == 0) {
            return 0;
        } else {
            return (int)round(($oldPrice - $newPrice) / $oldPrice * 100);
        }
    }

    public function getBaseCurrency()
    {
        return \Bitrix\Currency\CurrencyManager::getBaseCurrency();
    }
}
