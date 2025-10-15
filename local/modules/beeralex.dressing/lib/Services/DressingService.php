<?php

namespace Beeralex\Dressing\Services;

use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Sale\Basket;
use Bitrix\Sale\Order;
use App\Catalog\Basket\BasketFacade;
use App\Catalog\Helper\OrderHelper;
use App\Catalog\Helper\PersonTypeHelper;
use Beeralex\Dressing\Options;
use App\User\Services\AuthService;
use App\User\Phone\Phone;
use App\User\User;
use App\User\UserBuilder;

class DressingService
{
    public readonly Options $options;
    public readonly BasketFacade $basketFacade;

    public function __construct()
    {
        Loader::includeModule("sale");
        Loader::includeModule("catalog");

        $this->options = Options::getInstance();
        $this->basketFacade = BasketFacade::getForCurrentUser($this->options::FAKE_SITE_ID);
    }

    public function make(array $form): Order
    {
        $user = User::current();
        if (!$user->isAuthorized()) {
            $user = $this->addUser($form);
        }
        $basket = $this->basketFacade->getBasket();
        return $this->createOrder($user, $basket);
    }

    public function getDefaultForm(): array
    {
        $user = User::current();
        if ($user->isAuthorized()) {
            return [
                'name' => $user->getName(),
                'phone' => $user->getPhoneAsString(),
            ];
        }
        return [
            'name' => '',
            'phone' => '',
        ];
    }

    protected function addUser(array $form)
    {
        $phone = new Phone($form['phone']);
        $user = (new UserBuilder())
            ->setPhone($phone)
            ->setName($form['name'])
            ->build();
        (new AuthService())->register($user);
        return $user;
    }

    protected function createOrder(User $user, Basket $basket): Order
    {
        $order = Order::create(Context::getCurrent()->getSite(), $user->getId());
        $order->setPersonTypeId(PersonTypeHelper::getIndividualPersonId());
        $order->setBasket($basket);

        OrderHelper::addShipment($order);
        OrderHelper::addPayment($order, $this->options->pay);

        $this->setOrderProperties($order, $user);

        $order->setField('STATUS_ID', $this->options->status);

        $order->doFinalAction(true);
        $order->save();
        return $order;
    }

    protected function setOrderProperties(Order $order, User $user)
    {
        $propertyCollection = $order->getPropertyCollection();

        OrderHelper::setProperty($propertyCollection, 'NAME', $user->getName());
        OrderHelper::setProperty($propertyCollection, 'LAST_NAME', $user->getLastName() ?: '');
        OrderHelper::setProperty($propertyCollection, 'EMAIL', $user->getEmail());
        OrderHelper::setProperty($propertyCollection, 'PHONE', $user->getPhoneAsString());
        OrderHelper::setProperty($propertyCollection, 'IS_DRESSING', 'Y');
    }

    public function toggleBasketItem(int $offerId): string
    {
        $basketItems = $this->basketFacade->getExitstBasketItems($offerId);
        if (!empty($basketItems)) {
            $this->basketFacade->remove($offerId)->save();
            $action = 'delete';
        } else {
            $this->basketFacade->add($offerId)->save();
            $action = 'add';
        }
        return $action;
    }
}
