<?php
global $APPLICATION;

use Itb\Checkout\Delivery\PickPointDTO;
use Itb\Core\Helpers\WebHelper;

$result = [
    'error' => false,
    'data' => [],
    'message' => ''
];
if($arResult['PVZ'][$arResult['city']]){
    foreach($arResult['PVZ'][$arResult['city']] as $pvzId => $pvz){
        $dto = new PickPointDTO;
        $dto->id = $pvzId;
        $dto->city = $arResult['city'];
        $dto->name = $pvz['Name'];
        $dto->location = [
            'latitude' => $pvz['cY'],
            'longitude' => $pvz['cX']
        ];
        $dto->fitting = $pvz['Dressing'];
        $dto->cash = $pvz['Cash'];
        $dto->metro = $pvz['Metro'];
        $dto->address = $pvz['Address'];
        $dto->addressComment = $pvz['AddressComment'];
        $dto->station = $pvz['Station'];
        $dto->phone = $pvz['Phone'];
        $dto->schedule = $pvz['WorkTime'];
        $dto->weight = [
            'min' => $pvz['WeightLim']['MIN'],
            'max' => $pvz['WeightLim']['MAX'],
        ];
        $result['data']['points'][] = $dto;
    }
    $result['data']['total'] = [
        'price' => $arResult['DELIVERY']['pickup'],
        'date' => $arResult['DELIVERY']['p_date'],
    ];
} else {
    $result['error'] = true;
}

WebHelper::jsonAnswer($result);