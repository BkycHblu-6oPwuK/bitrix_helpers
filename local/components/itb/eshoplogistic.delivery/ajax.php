<?php

use Bitrix\Main\Config\Option;
use Bitrix\Main\Context;
use Bitrix\Main\Data\Cache;
use Bitrix\Main\Loader;
use Bitrix\Main\Web\Json;
use Eshoplogistic\Delivery\Config;
use Eshoplogistic\Delivery\Controller\AjaxHandler;
use Itb\Catalog\BasketFacade;

Loader::includeModule('eshoplogistic.delivery');

class ItbEshoplogisticDeliveryController extends AjaxHandler
{
    public function configureActions()
    {
        return array_merge(
            parent::configureActions(),
            [
                'calculateDeliveryDistance' => [
                    'prefilters' => []
                ],
                'getClient' => [
                    'prefilters' => []
                ],
            ]
        );
    }

    public static function getPvzListAction($profileId = '', $locationCode = '', $paymentId = 0)
    {
        try {
            $result = parent::getPvzListAction($profileId, $locationCode, $paymentId);
            $dtoList = [];
            if (isset($result[0]['terminals']) && is_array($result[0]['terminals'])) {
                foreach ($result[0]['terminals'] as $terminal) {
                    $dto = new \Itb\Checkout\Delivery\PickPointDTO();
                    $dto->id = $terminal['code'] ?? '';
                    $dto->name = $terminal['name'] ?? '';
                    $dto->city = $terminal['settlement'] ?? '';
                    $dto->address = $terminal['address'] ?? '';
                    $dto->addressComment = $terminal['address_details'] ?? '';
                    $dto->phone = $terminal['phones'] ?? '';
                    $dto->schedule = $terminal['workTime'] ?? '';
                    $dto->description = $terminal['note'] ?? '';
                    $dto->location = [
                        'latitude' => $terminal['lat'] ?? '',
                        'longitude' => $terminal['lon'] ?? ''
                    ];
                    $dto->images = isset($terminal['image']) ? [$terminal['image']] : [];
                    $dto->price = [
                        'value' => $terminal['price']['value'] ?? '',
                        'periodMin' => '',
                        'periodMax' => '',
                        'dateMin' => '',
                        'dateMax' => ''
                    ];
                    $dtoList[] = $dto;
                }
            }
            return [
                'success' => true,
                'points' => $dtoList
            ];
        } catch (\Exception $e) {
            return [
                'success' => false
            ];
        }
    }

    /**
     * @param float $distance км
     * @param float $duration часы
     * @param float $zone код зоны доставки
     */
    public static function calculateDeliveryDistanceAction(float $distance, float $duration)
    {
        try {
            $settings = Option::getForModule(Config::MODULE_ID);
            $weightDefault = $settings['weight_default'] ?: 5;
            $widthDefault = $settings['width_default'] ?: 100;
            $heightDefault = $settings['height_default'] ?: 100;
            $lengthDefault = $settings['length_default'] ?: 100;

            $basketItems = BasketFacade::getForCurrentUser()->getItems();
            $offers = [];
            foreach ($basketItems as $item) {
                $weight = $item['weight'] ? $item['weight'] / 1000 : $weightDefault;
                $width  = $item['width']  ? $item['width']  / 10   : $widthDefault;
                $height = $item['height'] ? $item['height'] / 10   : $heightDefault;
                $length = $item['length'] ? $item['length'] / 10   : $lengthDefault;
                $offers[] = [
                    'count' => $item['quantity'],
                    'price' => $item['price'],
                    'weight' => $weight,
                    'dimensions' => "{$width}*{$height}*{$length}",
                ];
            }

            $payload = [
                'zone' => '6878b27a21c5c',
                'distance' => $distance,
                'duration' => $duration,
                'offers' => Json::encode($offers),
            ];

            $cache_key = md5('delivery/distance:' . serialize($payload));
            $cache = Cache::createInstance();
            $cache_data = $cache->initCache(Config::CACHE_TIME, $cache_key, Config::CACHE_DIR);

            if (!empty($cache_data)) {
                $result = $cache->getVars();
            } elseif ($cache->startDataCache()) {
                $result = self::ApiQuery('delivery/distance', $payload);
                if (!empty($result['errors'])) {
                    throw new \RuntimeException($result['errors'][0]);
                }
                $cache->endDataCache($result);
            }

            return [
                'success' => true,
                'price' => $result['data']['price'],
            ];
        } catch (\RuntimeException $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
            ];
        }
    }

    public static function getClientAction()
    {
        $widgetKey = Option::get(Config::MODULE_ID, 'widget_key');
        $host = Context::getCurrent()->getServer()->getServerName();
        $key = "{$widgetKey}:" . md5($widgetKey . $host);
        $payload = ['key' => $key];
        $cache_key  = md5('widget/client' . $key);
        $cache = Cache::createInstance();
        $cache_data = $cache->initCache(Config::CACHE_TIME, $cache_key, Config::CACHE_DIR);

        if (!empty($cache_data)) {
            $data = $cache->getVars();
        } elseif ($cache->startDataCache()) {
            if ($requestOut = self::ApiQuery('widget/client', $payload)) {
                if (!empty($requestOut) && $requestOut['http_status'] == 200) {
                    $cache->endDataCache($requestOut);
                }
                $data = $requestOut;
            }
        }

        if ($data && $map = $data['data']['settings']['map']) {
            $result = [
                'location' => [
                    'latitude' => $map['center'][0],
                    'longitude' => $map['center'][1]
                ],
                'maxDistance' => $map['max_distance'],
                'maxDuration' => $map['max_duration'],
                'restrictArea' => $map['restrict_area'][0],
                'zone' => $map['zone'],
                'from' => $map['from']
            ];

            return [
                'success' => true,
                'data' => $result
            ];
        }

        return [
            'success' => false,
        ];
    }
}
