<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

use Bitrix\Main\Loader;
use Beeralex\Core\Service\LocationService;

class BeeralexSdekPvz extends \CBitrixComponent
{
    /** Название города из справочника СДЭК */
    private string $sdekCityName = '';

    public function executeComponent(): void
    {
        $locationCode = trim((string)($this->arParams['LOCATION_CODE'] ?? ''));

        if (empty($locationCode)) {
            $this->arResult = ['POINTS' => [], 'CITY' => ''];
            $this->includeComponentTemplate();
            return;
        }

        if (!Loader::includeModule('ipol.sdek')) {
            $this->arResult = ['POINTS' => [], 'CITY' => ''];
            $this->includeComponentTemplate();
            return;
        }

        $sdekCityCode = $this->resolveSdekCityCode($locationCode);

        if (empty($sdekCityCode)) {
            $this->arResult = ['POINTS' => [], 'CITY' => ''];
            $this->includeComponentTemplate();
            return;
        }

        $pointsController = new \Ipolh\SDEK\Bitrix\Controller\Points();
        $points = [];

        foreach (['PVZ', 'POSTAMAT'] as $type) {
            $result = $pointsController->getDeliveryPoints($sdekCityCode, $type, null);
            $data = $result->getData();

            foreach ($data['POINTS'] ?? [] as $code => $point) {
                $points[$code] = $point;
            }
        }

        $this->arResult = [
            'POINTS' => $points,
            'CITY'   => $this->sdekCityName,
        ];

        $this->includeComponentTemplate();
    }

    /**
     * Определяет SDEK CITY_CODE по коду Bitrix-локации.
     */
    private function resolveSdekCityCode(string $locationCode): ?int
    {
        /** @var LocationService $locationService */
        $locationService = service(LocationService::class);

        // Попытка 1: точное совпадение
        $location = $locationService->getLocationByCityCode($locationCode);
        if (!empty($location['ID'])) {
            $row = \sqlSdekCity::getByBId($location['ID']);
            if (!empty($row['SDEK_ID'])) {
                $this->sdekCityName = $row['NAME'] ?? '';
                return (int)$row['SDEK_ID'];
            }
        }

        // Попытка 2: поднимаемся по дереву до ближайшего города
        $nearestCity = $locationService->getNearestCityByLocationCode($locationCode);
        if (!empty($nearestCity['ID'])) {
            $row = \sqlSdekCity::getByBId($nearestCity['ID']);
            if (!empty($row['SDEK_ID'])) {
                $this->sdekCityName = $row['NAME'] ?? '';
                return (int)$row['SDEK_ID'];
            }
        }

        return null;
    }
}
