<?php
namespace Beeralex\Api\Domain\Checkout\Delivery;

use Bitrix\Sale\Delivery\Services\Base;

class BaseDelivery
{
    /**
     * @var array from sale.order.ajax $arResult['DELIVERY']
     */
    protected $delivery;
    /**
     * @var Base
     */
    protected $deliveryHandler;

    /**
     * @var array
     */
    protected $storeList;


    /**
     * @param $delivery array from sale.order.ajax $arResult['DELIVERY']
     *
     * @throws \Bitrix\Main\ArgumentNullException
     * @throws \Bitrix\Main\SystemException
     */
    public function __construct(array $delivery, \Bitrix\Sale\Delivery\Services\Base $handler)
    {
        if (!$delivery['ID']) {
            throw new \InvalidArgumentException('Delivery ID not specified');
        }
        $this->delivery = $delivery;
        $this->deliveryHandler = $handler;
        $this->storeList = \Bitrix\Sale\Delivery\ExtraServices\Manager::getStoresList($handler->getId());
    }


    /**
     * @return bool эта доставка выбрана в чекауте
     */
    public function isSelected(): bool
    {
        return $this->delivery['CHECKED'] == 'Y';
    }

    /**
     * @return bool is delivery can be choosed in checkout
     */
    public function isAvailable(): bool
    {
        return $this->isPriceCalculated();
    }

    /**
     * @return bool цена для доставки была успешно рассчитана
     */
    public function isPriceCalculated(): bool
    {
        return empty($this->delivery['CALCULATE_ERRORS']); //empty
    }

    /**
     * @return float min price for current location
     */
    public function getPrice(): float
    {
        return $this->delivery['PRICE'] ?? 0;
    }

    public function getDeliveryPeriod()
    {
        return $this->delivery['PERIOD_TEXT'] ?? '';
    }

    /**
     * @return string Delivery Service Code
     */
    public function getCode(): string
    {
        if ($this->deliveryHandler) {
            return $this->deliveryHandler->getCode();
        }
        return '';
    }

    /**
     * @return int Delivery Service ID
     */
    public function getId(): int
    {
        return $this->deliveryHandler->getId();
    }

    public function getName(): string
    {
        return $this->deliveryHandler->isProfile() ? $this->deliveryHandler->getNameWithParent() : $this->deliveryHandler->getName();
    }

    public function getOwnName(): string
    {
        return $this->deliveryHandler->getName();
    }

    public function getDescription(): string
    {
        return htmlspecialchars(strip_tags($this->deliveryHandler->getDescription()));
    }

    public function isStoreDelivery() : bool
    {
        return !empty($this->storeList);
    }

    public function getStoreList(): array
    {
        return $this->storeList;
    }

    public function getExtraServices(): array
    {
        return $this->deliveryHandler->getExtraServices()->getItems();
    }

    public function getLogotip()
    {
        return (int)$this->deliveryHandler->getLogotip() > 0 ? \CFile::GetPath($this->deliveryHandler->getLogotip()) : '';
    }

    public function getSort(): int
    {
        return $this->deliveryHandler->getSort();
    }
}
