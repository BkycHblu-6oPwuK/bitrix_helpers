<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Sdk\Api;

use Beeralex\Apiship\Sdk\Entity\Response\RawResponse;

/**
 * Методы специфичные для конкретных служб доставки.
 * OpenAPI: /dpd/*, /logsis/*, /x5/*, /yataxi/*, /ozondel/*, /boxberry/*, /cse/*
 */
class ProviderSpecific extends AbstractExtendedApi
{
    // --- DPD ---

    public function dpdBindPreorderPickup(array $body): RawResponse
    {
        return $this->requestPost('dpd/bindPreorderPickup', $body);
    }

    public function dpdCreateAddressCode(array $body): RawResponse
    {
        return $this->requestPost('dpd/createAddressCode', $body);
    }

    public function dpdUpdateAddressCode(array $body): RawResponse
    {
        return $this->requestPut('dpd/updateAddressCode', $body);
    }

    // --- Logsis ---

    public function logsisConfirmOrder(array $body): RawResponse
    {
        return $this->requestPost('logsis/confirmOrder', $body);
    }

    public function logsisUpdateWarehouse(array $body): RawResponse
    {
        return $this->requestPost('logsis/updateWarehouse', $body);
    }

    // --- X5 ---

    public function x5CreateWarehouse(array $body): RawResponse
    {
        return $this->requestPost('x5/createWarehouse', $body);
    }

    // --- Yataxi ---

    public function yataxiConfirm(array $body): RawResponse
    {
        return $this->requestPost('yataxi/confirm', $body);
    }

    public function yataxiRobotCoverClose(array $body): RawResponse
    {
        return $this->requestPost('yataxi/robot/cover/close', $body);
    }

    public function yataxiRobotCoverOpen(array $body): RawResponse
    {
        return $this->requestPost('yataxi/robot/cover/open', $body);
    }

    // --- Ozon Delivery (ozondel) ---

    public function ozondelCheckClient(array $body): RawResponse
    {
        return $this->requestPost('ozondel/check-client', $body);
    }

    // --- Boxberry ---

    public function boxberryCreateWarehouse(array $body): RawResponse
    {
        return $this->requestPost('boxberry/warehouse', $body);
    }

    public function boxberryGetWarehouse(string $code): RawResponse
    {
        return $this->requestGet('boxberry/warehouse/' . rawurlencode($code));
    }

    // --- CSE ---

    public function cseSaveOwnerOfTheGoods(array $body): RawResponse
    {
        return $this->requestPost('cse/saveOwnerOfTheGoods', $body);
    }
}
