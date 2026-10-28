<?php

declare(strict_types=1);

namespace Beeralex\Apiship\Dto;

use Beeralex\Core\Http\Resources\Resource;

/**
 * Пункт выдачи/приёма заказов (ПВЗ).
 *
 * @property string $id
 * @property string $providerKey
 * @property string $providerKeyOriginal
 * @property string $code           код ПВЗ у ТК
 * @property string $name
 * @property string $address        готовая строка адреса
 * @property string $city
 * @property string $cityGuid
 * @property string $postIndex
 * @property string $phone
 * @property string $type           тип ПВЗ
 * @property array  $availableOperation  массив кодов доступных операций
 * @property int|null $cod          наложенный платёж (1=да, 0=нет)
 * @property int|null $paymentCash
 * @property int|null $paymentCard
 * @property int|null $fittingRoom
 * @property array|null $workTime   массив расписания из API
 * @property float|null $lat
 * @property float|null $lng
 * @property string $url
 * @property string $email
 * @property string $timetable
 * @property string $description
 * @property array|null $photos
 * @property array|null $metro
 * @property array|null $extra
 * @property array|null $limits
 * @property bool $enabled
 * @property int|null $paymentOnline    оплата онлайн
 */
final class PointDto extends Resource
{
    public static function make(array $data): static
    {
        return new static([
            'id'                => (string)($data['id'] ?? ''),
            'providerKey'       => (string)($data['providerKey'] ?? ''),
            'providerKeyOriginal' => (string)($data['providerKeyOriginal'] ?? ''),
            'code'              => (string)($data['code'] ?? ''),
            'name'              => (string)($data['name'] ?? ''),
            'postIndex'         => (string)($data['postIndex'] ?? ''),
            'lat'               => isset($data['lat']) ? (float)$data['lat'] : null,
            'lng'               => isset($data['lng']) ? (float)$data['lng'] : null,
            'address'           => (string)($data['address'] ?? ''),
            'city'              => (string)($data['city'] ?? ''),
            'cityGuid'          => (string)($data['cityGuid'] ?? ''),
            'url'               => (string)($data['url'] ?? ''),
            'email'             => (string)($data['email'] ?? ''),
            'phone'             => (string)($data['phone'] ?? ''),
            'timetable'         => (string)($data['timetable'] ?? ''),
            'workTime'          => isset($data['workTime']) ? (array)$data['workTime'] : null,
            'fittingRoom'       => isset($data['fittingRoom']) ? (int)$data['fittingRoom'] : null,
            'cod'               => isset($data['cod']) ? (int)$data['cod'] : null,
            'paymentCash'       => isset($data['paymentCash']) ? (int)$data['paymentCash'] : null,
            'paymentCard'       => isset($data['paymentCard']) ? (int)$data['paymentCard'] : null,
            'paymentOnline'     => isset($data['paymentOnline']) ? (int)$data['paymentOnline'] : null,
            'availableOperation' => (array)($data['availableOperation'] ?? []),
            'type'              => (string)($data['type'] ?? ''),
            'description'       => (string)($data['description'] ?? ''),
            'photos'            => isset($data['photos']) ? (array)$data['photos'] : null,
            'metro'             => isset($data['metro']) ? (array)$data['metro'] : null,
            'extra'             => isset($data['extra']) ? (array)$data['extra'] : null,
            'limits'            => isset($data['limits']) ? (array)$data['limits'] : null,
            'enabled'           => isset($data['enabled']) ? (bool)$data['enabled'] : false
        ]);
    }
}
