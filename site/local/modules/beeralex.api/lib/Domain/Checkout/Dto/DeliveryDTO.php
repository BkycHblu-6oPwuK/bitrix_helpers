<?php

namespace Beeralex\Catalog\Checkout\Dto;

class DeliveryDTO
{
    /**
     * @var int
     */
    public $id = 0;

    /**
     * @var string
     */
    public $code = '';

    /**
     * @var string
     */
    public $name = '';

    /**
     * @var string
     */
    public $ownName = '';

    /**
     * @var string
     */
    public $description = '';

    /**
     * @var string
     */
    public $currency = '';

    /**
     * @var int
     */
    public $sort = 0;

    /**
     * @var array
     */
    public $extraServices = [];

    /**
     * @var bool
     */
    public $isStoreDelivery = false;

    /**
     * @var string
     */
    public $logotip = '';

    /**
     * @var bool
     */
    public $isSelected = false;

    /**
     * @var array
     */
    public $storeList = [];

    /**
     * @var float
     */
    public $price = 0.0;

    /**
     * @var string
     */
    public $deliveryPeriod = '';

    public $isTransport = false;
    public $isDoor = false;
    public $isOwnDelivery = false;
}
