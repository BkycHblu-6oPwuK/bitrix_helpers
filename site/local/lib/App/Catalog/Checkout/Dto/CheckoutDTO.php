<?php

namespace App\Catalog\Checkout\Dto;

/**
 * DTO данных для чекаута в формате необходимом фронту
 */
class CheckoutDTO
{
    /**
     * @var array Корзина
     */
    public $items;

    /**
     * @var array Инфа по общей цене
     */
    public $totalPrice;

    /**
     * @var FormDTO личные данные
     */
    public $form;

    /**
     * @var string код выбранной системы оплаты (код не самой системы оплаты, а код фронта)
     */
    public $activePay;

    /**
     * @var array
     */
    public $payments;

    /**
     * @var DeliveriesDTO
     */
    public $delivery;
    
    /**
     * @var array комментарий к заказу
     */
    public $comment = '';

    /**
     * @var string
     */
    public $personType;
    /**
     * @var string
     */
    public $profileId;

    /**
     * @var array field => idForCheckout используется для подстановки значений свойств в чекауте
     * в нужные POST параметры (для стандартной работы компонента sale.order.ajax)
     */
    public $propIdsMap;

    public $rules = [
        'checked' => true
    ];
    /**
     * @var CouponDTO
     */
    public $coupon;
}
