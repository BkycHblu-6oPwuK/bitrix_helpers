<?php

use Beeralex\Api\ApiResult;
use Beeralex\Api\Domain\Checkout\CheckoutDTOBuilder;
use Beeralex\Api\Domain\Checkout\DeliveriesBuilder;
use Beeralex\Api\Domain\Checkout\PaymentsBuilder;
use Beeralex\Api\Domain\Checkout\PersonTypeBuilder;
use Beeralex\Catalog\Location\Contracts\BitrixLocationResolverContract;
use Beeralex\Catalog\Service\OrderService;
use Beeralex\Core\Service\LocationService;
use Beeralex\User\Auth\AuthCredentialsDto;
use Beeralex\User\Auth\Contracts\EmailAuthenticatorContract;
use Beeralex\User\Contracts\UserRepositoryContract;
use Bitrix\Main\Loader;
use Bitrix\Main\Security\Sign\Signer;
use Bitrix\Sale\Order;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

CBitrixComponent::includeComponentClass('bitrix:sale.order.ajax');

class BeeralexSaleOrderAjax extends SaleOrderAjax
{
    /**
     * @var DeliveriesBuilder
     */
    protected $deliveriesBuilder;

    protected ?BitrixLocationResolverContract $locationResolver = null;
    protected ApiResult $apiResult;
    protected OrderService $orderService;

    public function executeComponent()
    {
        Loader::requireModule('beeralex.catalog');
        Loader::requireModule('beeralex.user');
        $this->locationResolver = service(BitrixLocationResolverContract::class);
        $this->apiResult = service(ApiResult::class);
        $this->orderService = service(OrderService::class);
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->addEventHandler('sale', 'OnSaleComponentOrderProperties', [$this, 'modifyOrderPropsBeforeDelivery']);
        parent::executeComponent();
    }

    /**
     * Переопределяем чтобы возвращать результат в api формате в виде объекта ApiResult
     */
    protected function showAjaxAnswer($result)
    {
        foreach (GetModuleEvents("sale", 'OnSaleComponentOrderShowAjaxAnswer', true) as $arEvent)
            ExecuteModuleEventEx($arEvent, [&$result]);

        if($result['error']) {
            $this->apiResult->addError(new \Bitrix\Main\Error($result['error']));
        } else {
            $this->apiResult->addPageData($result);
        }
    }

    protected function prepareResultArray()
    {
        $this->arResult['JS_DATA'] = (new CheckoutDTOBuilder())
            ->setOrder($this->order)
            ->setPaymentsBuilder($this->getPaymentsBuilder($this->order))
            ->setDeliveriesBuilder($this->deliveriesBuilder)
            ->setPersonTypeBuilder($this->getPersonTypeBuilder())
            ->setRules($this->getRequestedRules())
            ->setProfileId($this->arUserResult['PROFILE_ID'] ?? '')
            ->setSignedParameters((new Signer())->sign(base64_encode(serialize($this->arParams)), 'sale.order.ajax'))
            ->setSiteId($this->order->getSiteId())
            ->build();
    }

    private function getPaymentsBuilder(Order $order): PaymentsBuilder
    {
        $paySystemList = $this->arParams['DELIVERY_TO_PAYSYSTEM'] === 'p2d' ? $this->arActivePaySystems : $this->arPaySystemServiceAll;
        $selectedPayment = $this->getExternalPayment($order);
        $paymentId = $selectedPayment ? $selectedPayment->getPaymentSystemId() : 0;
        return new PaymentsBuilder($paySystemList, $paymentId);
    }

    private function getDeliveriesBuilder(\Bitrix\Sale\Order $order): DeliveriesBuilder
    {
        $deliveries = collect($this->arResult['DELIVERY'])
            ->map(function ($delivery, $deliveryId) {
                $delivery['ID'] = $deliveryId;
                return $delivery;
            });
        return new DeliveriesBuilder(
            $deliveries,
            $this->arDeliveryServiceAll,
            $order
        );
    }

    private function getPersonTypeBuilder(): PersonTypeBuilder
    {
        return new PersonTypeBuilder(
            $this->arResult['PERSON_TYPE'],
            $this->order->getPersonTypeId() ?? 0,
            (int)$this->arUserResult['PERSON_TYPE_OLD']
        );
    }

    /**
     * @return bool
     */
    private function getRequestedRules(): bool
    {
        return $this->request->get('rules') !== 'false';
    }

    protected function createOrder($userId)
    {
        $order = parent::createOrder($userId);

        if ($this->isOrderConfirmed) {
            if ($shipment = $this->getCurrentShipment($order)) {
                $this->arResult['DELIVERY'][$shipment->getDeliveryId()] = [
                    'ID'      => $shipment->getDeliveryId(),
                    'CHECKED' => true,
                    'PRICE'   => $order->getDeliveryPrice(),
                ];
            }
        }
        $this->deliveriesBuilder = $this->getDeliveriesBuilder($order);

        return $order;
    }

    protected function refreshOrderAjaxAction()
    {
        global $USER;
        $this->order = $this->createOrder($USER->GetID() ? $USER->GetID() : CSaleUser::GetAnonymousUserID());
        $this->prepareResultArray();

        $this->showAjaxAnswer([
            'order' => $this->arResult['JS_DATA'],
        ]);
    }

    protected function saveOrder($saveToSession = false)
    {
        /** @var \Bitrix\Sale\Shipment $shipment */
        $shipment = collect($this->order->getShipmentCollection()->getNotSystemItems())->first();

        if (
            $shipment &&
            $shipment->getDeliveryId() != $this->request->get('DELIVERY_ID')
        ) {
            $this->showAjaxAnswer([
                'error' => 'Выберите службу доставки'
            ]);
        }
        if (empty($this->order->getPaymentCollection())) {
            $this->showAjaxAnswer([
                'error' => 'Выберите систему оплаты'
            ]);
        }

        parent::saveOrder($saveToSession);
    }

    public function modifyOrderPropsBeforeDelivery(&$arUserResult)
    {
        $props = \Bitrix\Sale\Property::getList([
            'select' => ['ID', 'CODE'],
            'filter' => ['ID' => array_keys($arUserResult['ORDER_PROP'])]
        ])->fetchAll();
        $props = collect($props)
            ->mapWithKeys(function ($prop) {
                return [$prop['CODE'] => $prop];
            });
        $requestProperties = $this->getPropertyValuesFromRequest();
        $user = \service(UserRepositoryContract::class)->getCurrentUser();

        if ($this->locationResolver) {
            $variants = [];
            $oldLocation = $this->request->get('OLD_LOCATION');
            $requestAddress = $requestProperties[$props->get('ADDRESS')['ID']];
            if (empty($requestAddress) && !empty($oldLocation)) {
                $location = service(LocationService::class)->getNearestCityByLocationCode($oldLocation, 3600000);
                if (!empty($location)) {
                    $arUserResult['ORDER_PROP'][$props->get('CITY')['ID']] = $location['LOCATION_NAME_NAME'];
                    $arUserResult['ORDER_PROP'][$props->get('LOCATION')['ID']] = $location['CODE'];
                }
            } else {
                $requestCity = $requestProperties[$props->get('CITY')['ID']];
                $city = firstNotEmpty('', $requestCity, $arUserResult['ORDER_PROP'][$props->get('CITY')['ID']]);
                $address = firstNotEmpty('', $requestAddress, $arUserResult['ORDER_PROP'][$props->get('ADDRESS')['ID']]);
                if ($address) {
                    $variants[] = $address;
                }
                if ($city) {
                    $variants[] = $city;
                }

                foreach ($variants as $variant) {
                    $location = $this->locationResolver->getBitrixLocationByAddress($variant);
                    if ($location) {
                        $arUserResult['ORDER_PROP'][$props->get('CITY')['ID']] = $location['city'];
                        $arUserResult['ORDER_PROP'][$props->get('LOCATION')['ID']] = $location['code'];
                        break;
                    }
                }
            }
        }

        // заполняем данные по умолчанию из полей пользователя

        foreach ($props as $prop) {
            $val = $arUserResult['ORDER_PROP'][$prop['ID']];
            switch ($prop['CODE']) {
                case 'NAME':
                    if (empty($val)) {
                        $arUserResult['ORDER_PROP'][$prop['ID']] = $user->getName();
                    }
                    break;
                case 'LAST_NAME':
                    if (empty($val)) {
                        $arUserResult['ORDER_PROP'][$prop['ID']] = $user->getLastName();
                    }
                    break;
                case 'EMAIL':
                    if (empty($val)) {
                        $arUserResult['ORDER_PROP'][$prop['ID']] = $user->getEmail();
                    }
                    break;
                case 'PHONE':
                    if (empty($val)) {
                        $arUserResult['ORDER_PROP'][$prop['ID']] = $user->getPhoneAsString();
                    }
                    break;
                case 'FIO':
                    if (empty($val)) {
                        $arUserResult['ORDER_PROP'][$prop['ID']] = $user->getFullName();
                    }
                    break;
            }
        }
    }

    protected function getOrderProperties()
    {
        $propValues = $this->getPropertyValuesFromRequest();
        $orderProps = collect($this->orderService->getPropertyList($this->order->getPersonTypeId()))
            ->map(function ($prop) use ($propValues) {
                return array_merge($prop, ['VALUE' => $propValues[(int)$prop['ID']]]);
            });
        return $orderProps;
    }

    /**
     * @inheritdoc
     */
    protected function registerAndLogIn($userProps)
    {
        $userId = false;
        $userData = $this->generateUserData($userProps);
        if (!$userProps['NEW_PASSWORD']) {
            $userProps['NEW_PASSWORD'] = $userData['NEW_PASSWORD'];
            $userProps['NEW_PASSWORD_CONFIRM'] = $userData['NEW_PASSWORD_CONFIRM'];
        }
        if (!$userProps['GROUP_ID']) {
            $userProps['GROUP_ID'] = $userData['GROUP_ID'];
        }
        try {
            $authCredentialsDto = $this->makeAuthCredentialsDto($userProps);
            $emailAuthentificator = service(EmailAuthenticatorContract::class);
            $result = $emailAuthentificator->register($authCredentialsDto);
            if (!$result->isSuccess()) {
                $errors = $result->getErrors();
                $message = 'При регистрации пользователя произошла ошибка';
                if (!empty($errors)) {
                    $message = $errors[0]->getMessage();
                }
                $this->showAjaxAnswer([
                    'error' => $message,
                ]);
            }
            $userId = $result->getData()['userId'];
        } catch (Exception $e) {
            $message = 'При регистрации пользователя произошла ошибка';
            $this->showAjaxAnswer([
                'error' => $message,
            ]);
        }
        return $userId;
    }

    protected function makeAuthCredentialsDto(array $userProps): AuthCredentialsDto
    {
        $orderProps = $this->getOrderProperties();
        return new AuthCredentialsDto([
            'email' => $orderProps['EMAIL']['VALUE'],
            'phone' => $orderProps['PHONE']['VALUE'],
            'password' => $userProps['NEW_PASSWORD'],
            'name' => $orderProps['NAME']['VALUE'],
            'last_name' => $orderProps['LAST_NAME']['VALUE'],
            'group' => $userProps['GROUP_ID'],
        ]);
    }

    /**
     * здесь bitrix сносит свойство из альтернативного поля ввода локации, нам это не надо
     */
    protected function checkAltLocationProperty(Order $order, $useProfileProperties, array $profileProperties) {}
    protected function showEmptyBasket() {}
}
