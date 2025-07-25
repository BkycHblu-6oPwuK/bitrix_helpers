<?php
namespace Itb\Checkout\Delivery;

use Bitrix\Main\Loader;
use Itb\Delivery\Config\Builder;
use Itb\Delivery\Config\EnumField;
use Itb\Delivery\Config\Field;
use Itb\Delivery\Config\Tab;

Loader::includeModule('itb.delivery');

class DeliveryOwnHandler extends \Bitrix\Sale\Delivery\Services\Base
{
    protected static $isCalculatePriceImmediately = true;
    protected static $whetherAdminExtraServicesShow = false;
    protected static $canHasProfiles = true;

    public function __construct(array $initParams)
    {
        parent::__construct($initParams);
    }

    public static function getClassTitle()
    {
        return 'Собственная доставка';
    }

    public static function getClassDescription()
    {
        return 'Собственная доставка до точки';
    }

    public function isCalculatePriceImmediately()
    {
        return self::$isCalculatePriceImmediately;
    }

    public static function whetherAdminExtraServicesShow()
    {
        return self::$whetherAdminExtraServicesShow;
    }

    public function isCompatible(\Bitrix\Sale\Shipment $shipment)
    {
        $calcResult = self::calculateConcrete($shipment);
        return $calcResult->isSuccess();
    }

    protected function getConfigStructure()
    {
        return [
            'MAIN' => [
                'ZONE_ID' => [
                    'TYPE' => 'STRING',
                    'NAME' => 'ID зоны доставки'
                ]
            ]
        ];
        $configBuilder = new Builder();
        $mainTab = new Tab('MAIN', 'Основные', 'Основные настройки');
        $mainTab->addField(new Field('API_KEY', 'STRING', 'Ключ API'))
            ->addField(new Field('TEST_MODE', 'Y/N', 'Тестовый режим', 'N'))
            ->addField(new EnumField('PACKAGING_TYPE', 'Тип упаковки', [
                'BOX' => 'Коробка',
                'ENV' => 'Конверт'
            ], 'BOX'));

        $additionalTab = new Tab('ADDITIONAL', 'Дополнительные', 'Дополнительные настройки');
        $additionalTab->addField(new Field('CUSTOM_FIELD', 'STRING', 'Пользовательское поле', 'default_value'));

        $config = $configBuilder
            ->addTab($mainTab)
            ->addTab($additionalTab)
            ->getConfigStructure();
        return $config;
    }

    public static function canHasProfiles()
    {
        return self::$canHasProfiles;
    }

    protected function calculateConcrete(\Bitrix\Sale\Shipment $shipment = null)
    {
        throw new \Exception("Расчет производится только из профилей доставки");
    }

    public static function getChildrenClassNames()
    {
        return array(
            '\Itb\Delivery\Profiles\Pickup'
        );
    }

    public function getProfilesList()
    {
        return array("Новый профиль");
    }
}