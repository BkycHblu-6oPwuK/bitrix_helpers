<?php

use Bitrix\Main\Localization\Loc;
use Bitrix\Sale\Order as SaleOrder;
use Itb\Catalog\Location\Contracts\BitrixLocationResolverInterface;
use Itb\Checkout\CheckoutDTOBuilder;
use Itb\Checkout\DeliveriesBuilder;
use Itb\Catalog\Order;
use Itb\Checkout\PaymentsBuilder;
use Itb\Checkout\PersonTypeBuilder;
use Itb\User\Exceptions\ValidationException;
use Itb\User\Services\AuthService;
use Itb\User\Phone\Phone;
use Itb\User\User;
use Itb\User\UserBuilder;
use Itb\User\UserValidator;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

CBitrixComponent::includeComponentClass('bitrix:sale.order.ajax');

class ItbSaleOrderAjax extends SaleOrderAjax
{
    /**
     * @var DeliveriesBuilder
     */
    protected $deliveriesBuilder;

    protected ?BitrixLocationResolverInterface $locationResolver = null;

    public function executeComponent()
    {
        $this->locationResolver = \Bitrix\Main\DI\ServiceLocator::getInstance()->get(BitrixLocationResolverInterface::class);
        $eventManager = \Bitrix\Main\EventManager::getInstance();
        $eventManager->addEventHandler('sale', 'OnSaleComponentOrderProperties', [$this, 'modifyOrderPropsBeforeDelivery']);
        parent::executeComponent();
    }

    protected function prepareResultArray()
    {
        $this->arResult['JS_DATA']['checkoutDTO'] = (new CheckoutDTOBuilder())
            ->setOrder($this->order)
            ->setPaymentsBuilder($this->getPaymentsBuilder($this->order))
            ->setDeliveriesBuilder($this->deliveriesBuilder)
            ->setPersonTypeBuilder($this->getPersonTypeBuilder())
            ->setRules($this->getRequestedRules())
            ->setProfileId($this->arUserResult['PROFILE_ID'] ?? '')
            ->build();
    }

    /**
     * @param \Bitrix\Sale\Order $order
     *
     * @return PaymentsBuilder
     */
    private function getPaymentsBuilder(\Bitrix\Sale\Order $order): PaymentsBuilder
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
        $error = false;
        if ($this->checkSession) {
            $this->order = $this->createOrder($USER->GetID() ? $USER->GetID() : CSaleUser::GetAnonymousUserID());
            $this->prepareResultArray();
        } else {
            $error = Loc::getMessage('SESSID_ERROR');
        }

        $this->showAjaxAnswer([
            'order' => $this->arResult['JS_DATA'],
            'error' => $error,
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
        $user = User::current();

        if ($this->locationResolver) {
            $variants = [];
            $city = $requestProperties[$props->get('CITY')['ID']] ?? $arUserResult['ORDER_PROP'][$props->get('CITY')['ID']] ?? '';
            $address = $requestProperties[$props->get('ADDRESS')['ID']] ?? $arUserResult['ORDER_PROP'][$props->get('ADDRESS')['ID']] ?? '';
            if ($address) {
                if (mb_strpos(mb_strtolower($address), mb_strtolower($city)) === false) {
                    $address .= ', ' . $city;
                }
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
        $orderProps = collect(Order::getPropertyList($this->order->getPersonTypeId()))
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
        $orderProps = $this->getOrderProperties();
        try {
            $phone = new Phone($orderProps['PHONE']['VALUE']);
            $user = (new UserBuilder())
                ->setEmail($orderProps['EMAIL']['VALUE'])
                ->setName($orderProps['NAME']['VALUE'])
                ->setLastName($orderProps['LAST_NAME']['VALUE'])
                ->setPassword($userProps['NEW_PASSWORD'])
                ->setPhone($phone)
                ->setGroup($userProps['GROUP_ID'])
                ->build();
            $validator = new UserValidator();
            if (!$validator->validateUser($user, true)) {
                foreach ($validator->getErrors() as $error) {
                    if (is_array($error)) {
                        foreach ($error as $err) {
                            throw new ValidationException($err);
                        }
                    }
                    throw new ValidationException($error);
                }
            }
            (new AuthService())->register($user);
            $userId = $user->getId();
        } catch (Exception $e) {
            $message = 'При регистрации пользователя произошла ошибка';
            if ($e instanceof ValidationException) {
                $message = $e->getMessage();
            }
            $this->showAjaxAnswer([
                'error' => $message,
            ]);
        }
        return $userId;
    }

    /**
     * здесь bitrix сносит свойство из альтернативного поля ввода локации, нам это не надо
     */
    protected function checkAltLocationProperty(SaleOrder $order, $useProfileProperties, array $profileProperties) {}
}
