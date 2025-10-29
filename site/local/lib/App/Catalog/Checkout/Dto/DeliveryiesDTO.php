<?php

namespace App\Catalog\Checkout\Dto;

class DeliveryiesDTO
{
    /**
     * @var string
     */
    public $message = '';

    /**
     * @var string
     */
    public $location = '';

    /**
     * @var string
     */
    public $city = '';

    /**
     * @var string
     */
    public $address = '';

    /**
     * @var string
     */
    public $postCode = '';

    /**
     * @var string
     */
    public $selectedPvz = '';

    /**
     * @var array[] широта, долгота
     */
    public $coordinates = [];

    /**
     * @var string
     */
    public $completionDate = '';

    /**
     * @var DeliveryDTO[]
     */
    public $deliveries = [];

    /**
     * @var float минимальная цена доставка среди всех служб доставок
     */
    public $minDeliveryPrice = 0.0;

    /**
     * @var string
     */
    public $minDeliveryPriceFormatted = '';

    /**
     * @var int
     */
    public $selectedId = 0;

    /**
     * @var int
     */
    public $storeSelectedId = 0;

    /**
     * @var float
     */
    public $distance = 0;

    /**
     * @var float
     */
    public $duration = 0;
}
