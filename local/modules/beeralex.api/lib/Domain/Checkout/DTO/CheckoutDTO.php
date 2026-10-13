<?php

namespace Beeralex\Api\Domain\Checkout\DTO;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property CheckoutItemDTO[] $items
 * @property TotalDTO $total
 * @property string $activePay
 * @property PaymentDTO[] $payments
 * @property DeliveryiesDTO $delivery
 * @property string $comment
 * @property PropertyDTO[] $properties
 * @property PersonTypeDTO $personType
 * @property string $profileId
 * @property array $propIdsMap
 * @property array $rules
 * @property CouponDTO|null $coupon
 * @property string $signedParameters
 * @property string $siteId
 * @property bool $isAuthorized
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
            'total' => isset($data['total']) && is_array($data['total']) 
                ? TotalDTO::make($data['total']) 
                : $data['total'] ?? [],
            'activePay' => $data['activePay'] ?? '',
            'payments' => $payments,
            'delivery' => $data['delivery'],
            'comment' => $data['comment'] ?? '',
            'properties' => $data['properties'] ?? [],
            'personType' => $data['personType'],
            'profileId' => $data['profileId'] ?? '',
            'propIdsMap' => $data['propIdsMap'] ?? [],
            'rules' => $data['rules'] ?? ['checked' => false],
            'coupon' => $data['coupon'],
            'signedParameters' => $data['signedParameters'] ?? '',
            'siteId' => $data['siteId'] ?? '',
            'isAuthorized' => $data['isAuthorized'],
        ]);
    }
}
