<?php
namespace Beeralex\Api\Domain\Checkout\DTO;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property string $email
 * @property string $phone
 * @property string $fio
 * @property string $legalInn
 * @property string $legalName
 * @property string $legalAddress
 * @property bool $legalAddressCheck
 * @property string $legalActualAddress
 * 
 * DTO формы с данными пользователя
 */
class FormDTO extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'email' => $data['email'] ?? '',
            'phone' => $data['phone'] ?? '',
            'fio' => $data['fio'] ?? '',
            'legalInn' => $data['legalInn'] ?? '',
            'legalName' => $data['legalName'] ?? '',
            'legalAddress' => $data['legalAddress'] ?? '',
            'legalAddressCheck' => $data['legalAddressCheck'] ?? false,
            'legalActualAddress' => $data['legalActualAddress'] ?? '',
        ]);
    }
}
