<?php

namespace Beeralex\Api\Domain\Checkout\DTO;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property CheckoutItemDTO[] $items
 * @property TotalPriceDTO $totalPrice
 * @property FormDTO $form
 * @property string $activePay
 * @property PaymentDTO[] $payments
 * @property DeliveryiesDTO $delivery
 * @property string $comment
 * @property PersonTypeDTO $personType
 * @property string $profileId
 * @property array $propIdsMap
 * @property array $rules
 * @property CouponDTO|null $coupon
 * @property string $signedParameters
 * @property string $siteId
 * 
 * DTO данных для чекаута
 */
class CheckoutDTO extends Resource
{
    public static function make(array $data): static
    {
        $items = [];
        foreach ($data['items'] ?? [] as $item) {
            $items[] = CheckoutItemDTO::make($item);
        }

        $payments = [];
        foreach ($data['payments'] ?? [] as $key => $payment) {
            $payments[$key] = is_array($payment) ? PaymentDTO::make($payment) : $payment;
        }

        return new static([
            'items' => $items,
            'totalPrice' => isset($data['totalPrice']) && is_array($data['totalPrice']) 
                ? TotalPriceDTO::make($data['totalPrice']) 
                : $data['totalPrice'] ?? [],
            'form' => isset($data['form']) ? FormDTO::make($data['form']) : FormDTO::make([]),
            'activePay' => $data['activePay'] ?? '',
            'payments' => $payments,
            'delivery' => $data['delivery'],
            'comment' => $data['comment'] ?? '',
            'personType' => $data['personType'],
            'profileId' => $data['profileId'] ?? '',
            'propIdsMap' => $data['propIdsMap'] ?? [],
            'rules' => $data['rules'] ?? ['checked' => true],
            'coupon' => $data['coupon'],
            'signedParameters' => $data['signedParameters'] ?? '',
            'siteId' => $data['siteId'] ?? '',
        ]);
    }
}
