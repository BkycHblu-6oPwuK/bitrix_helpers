<?php
namespace Beeralex\Dressing;

final class Options 
{
    const MODULE_ID = 'beeralex.dressing';
    const FAKE_SITE_ID = 'dr';

    public readonly string $status;
    public readonly int $pay;
    public readonly int $delivery;

    private static ?Options $instance = null;

    private function __construct()
    {
        $options = \Bitrix\Main\Config\Option::getForModule(self::MODULE_ID);
        $this->status = $options['dressing_status'];
        $this->pay = $options['dressing_pay'];
        if(!$this->status || !$this->pay){
            throw new \Exception('Options not set');
        }
    }

    public static function getInstance(): Options
    {
        if(!self::$instance){
            self::$instance = new Options();
        }
        return self::$instance;
    }
}