<?php

namespace Beeralex\Catalog\Dto;

class PickPointDTO
{

    /** @var string $id */
    public $id;

    /** @var string $name */
    public $name;

    /** @var string $city */
    public $city;

    /** @var string $phone*/
    public $phone;

    /** @var string $email*/
    public $email;

    /** @var string $address */
    public $address;

    /** @var string $addressComment */
    public $addressComment;

    /** @var string $metro */
    public $metro;

    /** @var string */
    public $station;

    /** @var string $schedule */
    public $schedule;

    /** @var string $description */
    public $description;

    /** @var bool $card */
    public $card = false;

    /** @var bool $cash */
    public $cash = false;

    /** @var bool $fitting */
    public $fitting = false;

    /** @var bool $return */
    public $return = false;

    /** @var array $images */
    public $images = [];

    public $price = [
        'value' => '',
        'periodMin' => '',
        'periodMax' => '',
        'dateMin' => '',
        'dateMax' => '',
    ];

    public $location = [
        'latitude' => '',
        'longitude' => ''
    ];

    public $weight = [
        'min' => '',
        'max' => ''
    ];
}
