<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Sdk\Api;

use Beeralex\Apiship\Sdk\Entity\Response\RawResponse;

/**
 * Биллинг и чеки ApiShip.
 * OpenAPI: /billing/balance, /pay/receipt, /pay/receipt/{receiptId}
 */
class Billing extends AbstractExtendedApi
{
    /** Баланс лицевого счёта. */
    public function getBalance(): RawResponse
    {
        return $this->requestGet('billing/balance');
    }

    /** Отправить данные онлайн-чека. */
    public function sendReceipt(array $body): RawResponse
    {
        return $this->requestPost('pay/receipt', $body);
    }

    /** Статус онлайн-чека. */
    public function getReceiptStatus(int $receiptId): RawResponse
    {
        return $this->requestGet('pay/receipt/' . $receiptId);
    }
}
