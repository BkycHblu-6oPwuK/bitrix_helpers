<?php
namespace Beeralex\Api\Domain\Checkout;

use Bitrix\Sale\BasketBase;
use Bitrix\Sale\PropertyValueCollectionBase;
use Beeralex\Api\Domain\Checkout\DTO\CheckoutDTO;
use Beeralex\Api\Domain\Checkout\DTO\CouponDTO;
use Beeralex\Api\Domain\Checkout\DTO\DeliveryiesDTO;
use Beeralex\Api\Domain\Checkout\DTO\FormDTO;
use Beeralex\Api\Domain\Checkout\DTO\PropertyDTO;
use Beeralex\Catalog\Service\OrderService;
use Bitrix\Sale\Order;

class CheckoutDTOBuilder
{
    private Order $order;
    private BasketBase $basket;
    private string $profileId;
    private PaymentsBuilder $paymentsBuilder;
    private DeliveriesBuilder $deliveriesBuilder;
    private PersonTypeBuilder $personTypeBuilder;
    private PropertiesBuilder $propertiesBuilder;
    private bool $rules;
    private string $signedParameters;
    private string $siteId;
    private OrderService $orderService;

    public function __construct()
    {
        $this->orderService = \service(OrderService::class);
    }

    public function setRules(bool $rules): static
    {
        $this->rules = $rules;
        return $this;
    }

    /**
     * @throws \Bitrix\Main\ArgumentException
     * @throws \Bitrix\Main\NotImplementedException
     */
    public function setOrder(Order $order): static
    {
        $this->order = $order;
        $this->basket = $order->getBasket();
        $this->propertiesBuilder = new PropertiesBuilder($order->getPropertyCollection(), (int)$order->getPersonTypeId());
        return $this;
    }

    public function setProfileId(string $profileId): static
    {
        $this->profileId = $profileId;
        return $this;
    }

    public function setPaymentsBuilder(PaymentsBuilder $paymentsBuilder): static
    {
        $this->paymentsBuilder = $paymentsBuilder;
        return $this;
    }

    public function setPersonTypeBuilder(PersonTypeBuilder $personTypeBuilder): static
    {
        $this->personTypeBuilder = $personTypeBuilder;
        return $this;
    }

    public function setDeliveriesBuilder(DeliveriesBuilder $builder): static
    {
        $this->deliveriesBuilder = $builder;
        return $this;
    }

    public function setSignedParameters(string $signedParameters): static
    {
        $this->signedParameters = $signedParameters;
        return $this;
    }

    public function setSiteId(string $siteId): static
    {
        $this->siteId = $siteId;
        return $this;
    }

    public function build(): CheckoutDTO
    {
        $basketData = $this->buildBasket();
        $totalPrice = (new TotalBuilder())->build($this->order, $basketData['SUMMARY']);

        return CheckoutDTO::make([
            'items' => $basketData['items'],
            'delivery' => $this->buildDeliveries($this->deliveriesBuilder),
            'totalPrice' => $totalPrice,
            'coupon' => $this->buildCoupon($basketData['COUPON']),
            'comment' => $this->buildComment(),
            'payments' => $this->paymentsBuilder->buildPayments(),
            'activePay' => $this->paymentsBuilder->getSelectedPaymentDTOKey(),
            'rules' => $this->buildRules(),
            'personType' => $this->personTypeBuilder->build(),
            'properties' => $this->propertiesBuilder->build(),
            'profileId' => $this->profileId,
            'propIdsMap' => $this->buildPropIdsMap(),
            'signedParameters' => $this->signedParameters,
            'siteId' => $this->siteId,
        ]);
    }

    private function buildDeliveries(DeliveriesBuilder $builder): DeliveryiesDTO
    {
        return $builder->buildDeliveriesDTO();
    }

    private function buildBasket(): array
    {
        return (new BasketBuilder())->buildForBasket($this->basket);
    }

    private function buildCoupon(string $coupon): CouponDTO
    {
        return CouponDTO::make([
            'value' => $coupon,
            'isVerified' => (bool)$coupon,
            'discount' => 0,
        ]);
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

    private function buildPropIdsMap(): array 
    {
        // ACTION saveOrderAjax
        $map = [
            'personType' => 'PERSON_TYPE',
            'personTypeOld' => 'PERSON_TYPE_OLD',
            'paySystem' => 'PAY_SYSTEM_ID',
            'delivery' => 'DELIVERY_ID',
            'profileId' => 'PROFILE_ID',
            'profileChange' => 'profile_change',
            'extraServices' => 'DELIVERY_EXTRA_SERVICES',
            'locationType' => 'location_type', // code
            'locationModeSteps' => 'PERMANENT_MODE_STEPS', // 0|1

            // comment
            'comment' => 'ORDER_DESCRIPTION',

            // payments and deliveries
            'payments'  => $this->paymentsBuilder->buildIdsMap(),
            'personTypes' => $this->personTypeBuilder->buildIdsMap(),
            'properties' => $this->propertiesBuilder->buildIdsMap(),
            'shopId' => 'BUYER_STORE',

            // location
            'postCodeChanged' => 'ZIP_PROPERTY_CHANGED', // Y|N
            'oldLocation' => 'OLD_LOCATION',
        ];
        return $map;
    }
}
