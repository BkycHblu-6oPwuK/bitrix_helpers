<?php

namespace Beeralex\Catalog\Dto;

class PickPointDTO
{
    public string $id;
    public string $name;
    public string $city;
    public string $phone;
    public string $email;
    public string $address;
    public string $addressComment;
    public string $metro;
    public string $station;
    public $schedule;
    public string $description;
    public $card = false;
    public bool $cash = false;
    public bool$fitting = false;
    public bool $return = false;
    public array $images = [];
    public array $price = [
        'value' => '',
        'periodMin' => '',
        'periodMax' => '',
        'dateMin' => '',
        'dateMax' => '',
    ];
    public array $location = [
        'latitude' => '',
        'longitude' => ''
    ];
    public array $weight = [
        'min' => '',
        'max' => ''
    ];
}
