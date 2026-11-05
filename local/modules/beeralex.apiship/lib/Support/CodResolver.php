<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Support;

use Bitrix\Sale\Order;

class CodResolver
{
    /** Сумма неоплаченных платежей наличной платёжной системы. > 0 — наложенный платёж. */
    public static function sum(Order $order): float
    {
        $sum = 0.0;
        foreach ($order->getPaymentCollection() as $payment) {
            if (!$payment->isPaid() && $payment->getPaySystem()?->isCash()) {
                $sum += (float)$payment->getSum();
            }
        }
        return $sum;
    }
}
