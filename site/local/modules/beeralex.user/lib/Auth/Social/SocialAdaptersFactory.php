<?php
declare(strict_types=1);
namespace Beeralex\User\Auth\Social;

use Beeralex\User\Auth\Social\BitrixSocialServiceAdapter;
use Bitrix\Main\Config\Option;

class SocialAdaptersFactory
{
    /**
     * Создаем адаптеры для всех включенных соц. сервисов.
     * @return BitrixSocialServiceAdapter[]
     */
    public function makeAll(): array
    {
        $option = Option::get('socialservices', 'auth_services', '');

        if (!$option) {
            return [];
        }

        $services = @unserialize($option, ['allowed_classes' => false]);
        
        if (!is_array($services)) {
            return [];
        }

        $result = [];
        foreach ($services as $serviceCode => $isEnabled) {
            try {
                $adapter = new BitrixSocialServiceAdapter($serviceCode, $isEnabled === 'Y');
                $result[$serviceCode] = $adapter;
            } catch (\Throwable $e) {
                // можно залогировать ошибку, но не падать
            }
        }

        return $result;
    }
}
