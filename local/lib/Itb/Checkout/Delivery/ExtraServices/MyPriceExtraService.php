<?php

namespace Itb\Checkout\Delivery\ExtraServices;

use Bitrix\Main\Loader;
use Bitrix\Sale\Delivery\ExtraServices\Base;
use Bitrix\Sale\Shipment;

Loader::includeModule('sale');

class MyPriceExtraService extends Base
{
    public function __construct($id, array $initParams, $currency, $value = null, array $additionalParams = array())
    {
        parent::__construct($id, $initParams, $currency, $value, $additionalParams);
        $this->params["TYPE"] = "STRING";
    }
    public static function getClassTitle(): string
    {
        return 'Сервис для установки определенной цены в доставку';
    }

    public function getCostShipment(?Shipment $shipment = null): float
    {
        return $this->convertToOperatingCurrency(
            (float)$this->value
        );
    }

    public function getPriceShipment(?Shipment $shipment = null): float
    {
        return $this->convertToOperatingCurrency(
            (float)$this->value
        );
    }

    public static function getCurDir(): string
    {
        return __DIR__;
    }

    protected function getEditHtml($selectedId, $prefix)
    {
        return '';
    }

    protected function getViewHtml($selectedCode)
    {
        return '';
    }

    public function getEditControl($prefix = "", $value = false)
    {
        return '';
    }
}
