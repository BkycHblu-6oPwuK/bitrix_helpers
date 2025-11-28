<?php
namespace Beeralex\Catalog\Checkout;

use Bitrix\Sale\BasketBase;
use Bitrix\Sale\PropertyValueCollectionBase;
use Beeralex\Catalog\Location\Contracts\BitrixLocationResolverContract;
use Beeralex\Catalog\Helper\OrderHelper;
use Beeralex\Catalog\Helper\PriceHelper;
use Beeralex\Catalog\Checkout\Dto\CheckoutDTO;
use Beeralex\Catalog\Checkout\Dto\CouponDTO;
use Beeralex\Catalog\Checkout\Dto\DeliveryiesDTO;
use Beeralex\Catalog\Checkout\Dto\FormDTO;

class CheckoutDTOBuilder
{
    const DELIVERY_EXTRA_SERVICES_REQUEST_KEY = 'DELIVERY_EXTRA_SERVICES';
    /**
     * @var \Bitrix\Sale\Order
     */
    private $order;
    /**
     * @var BasketBase
     */
    private $basket;
    /**
     * @var string
     */
    private $profileId;
    /**
     * @var PropertyValueCollectionBase
     */
    private $orderProperties;
    /**
     * @var PaymentsBuilder
     */
    private $paymentsBuilder;

    /**
     * @var DeliveriesBuilder
     */
    private $deliveriesBuilder;

    /**
     * @var PersonTypeBuilder
     */
    private $personTypeBuilder;
    /**
     * @var bool Правила обработки информации
     */
    private $rules;


    /**
     * @param bool $rules
     *
     * @return CheckoutDTOBuilder
     */
    public function setRules(bool $rules): self
    {
        $this->rules = $rules;
        return $this;
    }


    /**
     * @param \Bitrix\Sale\Order $order
     *
     * @return CheckoutDTOBuilder
     *
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\NotImplementedException
     */
    public function setOrder(\Bitrix\Sale\Order $order): self
    {
        $this->order = $order;
        $this->basket = $order->getBasket();
        $this->orderProperties = $order->getPropertyCollection();
        return $this;
    }


    /**
     * @param string $profileId
     *
     * @return CheckoutDTOBuilder
     */
    public function setProfileId(string $profileId): self
    {
        $this->profileId = $profileId;
        return $this;
    }


    /**
     * @param PaymentsBuilder $paymentsBuilder
     *
     * @return CheckoutDTOBuilder
     */
    public function setPaymentsBuilder(PaymentsBuilder $paymentsBuilder): self
    {
        $this->paymentsBuilder = $paymentsBuilder;
        return $this;
    }

    public function setPersonTypeBuilder(PersonTypeBuilder $personTypeBuilder): self
    {
        $this->personTypeBuilder = $personTypeBuilder;
        return $this;
    }


    /**
     * @param DeliveriesBuilder $builder
     *
     * @return CheckoutDTOBuilder
     */
    public function setDeliveriesBuilder(DeliveriesBuilder $builder): self
    {
        $this->deliveriesBuilder = $builder;
        return $this;
    }


    /**
     * @return CheckoutDTO
     */
    public function build(): CheckoutDTO
    {
        $dto = new CheckoutDTO();

        $basketData = $this->buildBasket();
        $this->buildTotal($basketData['summary']);  

        $dto->items = $basketData['items'];

        $dto->delivery = $this->buildDeliveries($this->deliveriesBuilder);

        $dto->totalPrice = $basketData['summary'];
        $dto->coupon = $this->buildCoupon($basketData['coupon']);

        $dto->form = $this->buildProfileProps();
        $dto->comment = $this->buildComment();

        $dto->payments = $this->paymentsBuilder->buildPayments();
        $dto->activePay = $this->paymentsBuilder->getSelectedPaymentDTOKey();

        $dto->rules = $this->buildRules();

        $dto->personType = $this->personTypeBuilder->build();
        $dto->profileId = $this->profileId;
        $dto->propIdsMap = $this->buildPropIdsMap($this->paymentsBuilder, $this->personTypeBuilder);

        return $dto;
    }


    private function buildDeliveries(DeliveriesBuilder $builder): DeliveryiesDTO
    {
        return $builder->buildDeliveriesDTO();
    }

    private function buildProfileProps(): FormDTO
    {
        return (new FormBuilder())->buildForCollection($this->orderProperties);
    }

    private function buildBasket(): array
    {
        return (new BasketBuilder())->buildForBasket($this->basket);
    }

    private function buildCoupon(string $coupon): CouponDTO
    {
        $dto = new CouponDTO;
        $dto->value = $coupon;
        $dto->isVerified = !!$coupon;
        return $dto;
    }

    private function buildComment(): string
    {
        return $this->order->getField('USER_DESCRIPTION') ?: '';
    }

    private function buildRules(): array
    {
        return [
            'checked' => $this->rules
        ];
    }

    private function buildTotal(array &$basketSummary)
    {
        $basketSummary['deliveryPrice'] = $this->order->getDeliveryPrice();
        $basketSummary['deliveryPriceFormatted'] = PriceHelper::format($basketSummary['deliveryPrice']);
        $basketSummary['totalItemsPrice'] = $basketSummary['totalPrice'];
        $basketSummary['totalItemsPriceFormatted'] = $basketSummary['totalPriceFormatted'];
        $basketSummary['totalPrice'] = $basketSummary['deliveryPrice'] + $basketSummary['totalItemsPrice'];
        $basketSummary['totalPriceFormatted'] = PriceHelper::format($basketSummary['totalPrice']);
    }

    private function buildPropIdsMap(
        PaymentsBuilder $paymentsBuilder,
        PersonTypeBuilder $personTypeBuilder,
    ): array {
        $props = OrderHelper::getProperties($this->orderProperties);
        // ACTION saveOrderAjax
        $map = [
            'personType' => 'PERSON_TYPE',
            'personTypeOld' => 'PERSON_TYPE_OLD',
            'paySystem' => 'PAY_SYSTEM_ID',
            'delivery' => 'DELIVERY_ID',
            'profileId' => 'PROFILE_ID',
            'profileChange' => 'profile_change',
            'extraServices' => static::DELIVERY_EXTRA_SERVICES_REQUEST_KEY,
            'locationType' => 'location_type', // code
            'locationModeSteps' => 'PERMANENT_MODE_STEPS', // 0|1

            // profile
            'email' => $props['EMAIL'] ? 'ORDER_PROP_' . $props['EMAIL']->getPropertyId() : '',
            'phone' => $props['PHONE'] ? 'ORDER_PROP_' . $props['PHONE']->getPropertyId() : '',
            'fio' => $props['FIO'] ? 'ORDER_PROP_' . $props['FIO']->getPropertyId() : '',
            'legalInn' => $props['INN'] ? 'ORDER_PROP_' . $props['INN']->getPropertyId() : '',
            'legalName' => $props['COMPANY'] ? 'ORDER_PROP_' . $props['COMPANY']->getPropertyId() : '',
            'legalAddress' => $props['COMPANY_ADR'] ? 'ORDER_PROP_' . $props['COMPANY_ADR']->getPropertyId() : '',
            'legalActualAddress' => $props['ACTUAL_ADR'] ? 'ORDER_PROP_' . $props['ACTUAL_ADR']->getPropertyId() : '',
            'legalAddressCheck' => $props['LEGAL_ADR_CHECK'] ? 'ORDER_PROP_' . $props['LEGAL_ADR_CHECK']->getPropertyId() : '',

            // comment
            'comment' => 'ORDER_DESCRIPTION',

            // payments and deliveries
            'payments'  => $paymentsBuilder->buildIdsMap(),
            'personTypes' => $personTypeBuilder->buildIdsMap(),
            'shopId' => 'BUYER_STORE',

            // location
            'city' => $props['CITY'] ? 'ORDER_PROP_' . $props['CITY']->getPropertyId() : '',
            'location' => $props['LOCATION'] ? 'ORDER_PROP_' . $props['LOCATION']->getPropertyId() : '',
            'address' => $props['ADDRESS'] ? 'ORDER_PROP_' . $props['ADDRESS']->getPropertyId() : '',
            'postCode' => $props['ZIP'] ? 'ORDER_PROP_' . $props['ZIP']->getPropertyId() : '',
            'eshoplogisticPvz' => $props['ESHOPLOGISTIC_PVZ'] ? 'ORDER_PROP_' . $props['ESHOPLOGISTIC_PVZ']->getPropertyId() : '',
            'eshoplogisticAddress' => $props['ESHOPLOGISTIC_FULL_ADDRESS'] ? 'ORDER_PROP_' . $props['ESHOPLOGISTIC_FULL_ADDRESS']->getPropertyId() : '',
            'completionDate' => $props['COMPLETION_DATE'] ? 'ORDER_PROP_' . $props['COMPLETION_DATE']->getPropertyId() : '',
            'coordinates' => $props['COORDINATES'] ? 'ORDER_PROP_' . $props['COORDINATES']->getPropertyId() : '',
            'postCodeChanged' => 'ZIP_PROPERTY_CHANGED', // Y|N
            'distance' => $props['DISTANCE'] ? 'ORDER_PROP_' . $props['DISTANCE']->getPropertyId() : '',
            'duration' => $props['DURATION'] ? 'ORDER_PROP_' . $props['DURATION']->getPropertyId() : '',
            'oldLocation' => 'OLD_LOCATION',
        ];
        return $map;
    }
}
