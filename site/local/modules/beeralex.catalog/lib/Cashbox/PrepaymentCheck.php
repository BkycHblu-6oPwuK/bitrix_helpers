<?php
declare(strict_types=1);
namespace Beeralex\Catalog\Cashbox;

use Bitrix\Sale\Cashbox\Check;
use Bitrix\Sale\PriceMaths;

/**
 * Исправленное округление в частичной предоплате
 */
class PrepaymentCheck extends Check
{
    public static function getType(): string
    {
        return 'prepayment';
    }

    public static function getCalculatedSign(): string
    {
        return static::CALCULATED_SIGN_INCOME;
    }

    public static function getName(): string
    {
        return 'Частичная предоплата custom';
    }

    public static function getSupportedEntityType(): string
    {
        return static::SUPPORTED_ENTITY_TYPE_PAYMENT;
    }

    public static function getSupportedRelatedEntityType(): string
    {
        return static::SUPPORTED_ENTITY_TYPE_SHIPMENT;
    }

    /**
     * @throws Main\ArgumentException
     * @throws Main\ArgumentNullException
     * @throws Main\ArgumentOutOfRangeException
     * @throws Main\ArgumentTypeException
     * @throws Main\LoaderException
     * @throws Main\NotImplementedException
     * @throws Main\ObjectPropertyException
     * @throws Main\SystemException
     */
    protected function extractDataInternal(): array
    {
        $result = parent::extractDataInternal();
        $result = $this->correlatePrices($result);

        foreach ($result['PRODUCTS'] as $i => $item) {
            $result['PRODUCTS'][$i]['PAYMENT_OBJECT'] = static::PAYMENT_OBJECT_PAYMENT;
        }

        if (!empty($result['DELIVERY']) && is_array($result['DELIVERY'])) {
            foreach ($result['DELIVERY'] as $i => $item) {
                $result['DELIVERY'][$i]['PAYMENT_OBJECT'] = static::PAYMENT_OBJECT_PAYMENT;
            }
        }
        return $result;
    }

    protected function needPrintMarkingCode($basketItem): bool
    {
        return false;
    }

    private function correlatePrices(array $result): array
    {
        $paymentSum = 0;
        foreach ($result['PAYMENTS'] as $payment) {
            $paymentSum += $payment['SUM'];
        }

        /** @var Order $order */
        $order = $result['ORDER'];

        $rate = $paymentSum / $order->getPrice();

        $countProductPositions = $result['PRODUCTS'] ? count($result['PRODUCTS']) : 0;
        $countDeliveryPositions = $result['DELIVERY'] ? count($result['DELIVERY']) : 0;

        if ($countDeliveryPositions === 0) {
            $totalSum = 0;
            for ($i = 0; $i < $countProductPositions - 1; $i++) {
                $totalSum += $this->correlatePosition($result['PRODUCTS'][$i], $rate);
            }

            if (isset($result['PRODUCTS'])) {
                $lastElement = $countProductPositions - 1;
                $result['PRODUCTS'][$lastElement]['SUM'] = PriceMaths::roundPrecision($paymentSum - $totalSum);
                $price = PriceMaths::roundPrecision($result['PRODUCTS'][$lastElement]['SUM'] / $result['PRODUCTS'][$lastElement]['QUANTITY']);
                $result['PRODUCTS'][$lastElement]['BASE_PRICE'] = $result['PRODUCTS'][$lastElement]['PRICE'] = $price;

                if (isset($result['PRODUCTS'][$lastElement]['DISCOUNT'])) {
                    unset($result['PRODUCTS'][$lastElement]['DISCOUNT']);
                }
            }
        } else {
            $totalSum = 0;
            for ($i = 0; $i < $countProductPositions; $i++) {
                $totalSum += $this->correlatePosition($result['PRODUCTS'][$i], $rate);
            }

            if ($countDeliveryPositions === 1) {
                $result['DELIVERY'][0]['SUM'] = PriceMaths::roundPrecision($paymentSum - $totalSum);
                $price = PriceMaths::roundPrecision($result['DELIVERY'][0]['SUM'] / $result['DELIVERY'][0]['QUANTITY']);
                $result['DELIVERY'][0]['BASE_PRICE'] = $result['DELIVERY'][0]['PRICE'] = $price;

                if (isset($result['DELIVERY'][0]['DISCOUNT'])) {
                    unset($result['DELIVERY'][0]['DISCOUNT']);
                }
            } else {
                for ($i = 0; $i < $countDeliveryPositions - 1; $i++) {
                    $totalSum += $this->correlatePosition($result['DELIVERY'][$i], $rate);
                }

                if (isset($result['DELIVERY'])) {
                    $lastElement = $countDeliveryPositions - 1;
                    $result['DELIVERY'][$lastElement]['SUM'] = PriceMaths::roundPrecision($paymentSum - $totalSum);
                    $price = PriceMaths::roundPrecision($result['DELIVERY'][$lastElement]['SUM'] / $result['DELIVERY'][$lastElement]['QUANTITY']);
                    $result['DELIVERY'][$lastElement]['BASE_PRICE'] = $result['DELIVERY'][$lastElement]['PRICE'] = $price;

                    if (isset($result['DELIVERY'][$lastElement]['DISCOUNT'])) {
                        unset($result['DELIVERY'][$lastElement]['DISCOUNT']);
                    }
                }
            }
        }

        return $result;
    }

    private function correlatePosition(array &$item, float $rate): float
    {
        $quantity = $item['QUANTITY'];
        $sum = PriceMaths::roundPrecision($item['SUM'] * $rate);
        $price = PriceMaths::roundPrecision($sum / $quantity);
        $calculatedSum = PriceMaths::roundPrecision($price * $quantity);
        if ($calculatedSum !== $sum) {
            $sum = $calculatedSum;
        }

        $item['SUM'] = $sum;
        $item['BASE_PRICE'] = $item['PRICE'] = $price;

        if (isset($item['DISCOUNT'])) {
            unset($item['DISCOUNT']);
        }

        return $sum;
    }
}
