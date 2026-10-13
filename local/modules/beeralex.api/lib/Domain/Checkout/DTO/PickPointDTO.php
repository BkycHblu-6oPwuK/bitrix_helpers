<?php
namespace Beeralex\Api\Domain\Checkout\DTO;

use Beeralex\Core\Http\Resources\Resource;

/**
 * @property string|int $id Идентификатор пункта выдачи
 * @property string $city Название города
 * @property string $name Название пункта выдачи
 * @property array $location Координаты пункта выдачи (latitude, longitude)
 * @property mixed $fitting Возможность примерки
 * @property mixed $cash Возможность оплаты наличными
 * @property string $metro Ближайшее метро
 * @property string $address Адрес пункта выдачи
 * @property string $addressComment Комментарий к адресу
 * @property string $station Ближайшая железнодорожная станция
 * @property string $phone Контактный телефон пункта выдачи
 * @property string $schedule Режим работы пункта выдачи
 * @property array $weight Минимальный и максимальный вес посылки (min, max)
 * 
 * DTO пункта выдачи PickPoint
 */
class PickPointDTO extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'id' => $data['id'] ?? '',
            'city' => $data['city'] ?? '',
            'name' => $data['name'] ?? '',
            'location' => [
                'latitude' => $data['location']['latitude'] ?? null,
                'longitude' => $data['location']['longitude'] ?? null,
            ],
            'fitting' => $data['fitting'] ?? null,
            'cash' => $data['cash'] ?? null,
            'metro' => $data['metro'] ?? '',
            'address' => $data['address'] ?? '',
            'addressComment' => $data['addressComment'] ?? '',
            'station' => $data['station'] ?? '',
            'phone' => $data['phone'] ?? '',
            'schedule' => $data['schedule'] ?? '',
            'weight' => [
                'min' => $data['weight']['min'] ?? null,
                'max' => $data['weight']['max'] ?? null,
            ],
        ]);
    }
}