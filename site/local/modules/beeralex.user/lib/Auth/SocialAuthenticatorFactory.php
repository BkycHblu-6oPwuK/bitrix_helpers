<?php

namespace Beeralex\User\Auth;

use Beeralex\User\Auth\Authenticators\SocialServiceAuthenticator;
use Beeralex\User\Auth\Social\SocialManager;

class SocialAuthenticatorFactory
{
    /**
     * Возвращает список всех активных соц. аутентификаторов.
     *
     * @return SocialServiceAuthenticator[]
     */
    public function makeAll(): array
    {
        $adapters = service(SocialManager::class)->adapters;
        $result = [];
        foreach ($adapters as $key => $adapter) {
            if(!$adapter->isEnable) continue;
            try {
                $authenticator = new SocialServiceAuthenticator($adapter);
                $result[static::formatKey($authenticator->getKey())] = $authenticator;
            } catch (\Throwable $e) {
                // можно залогировать ошибку, но не падать  
            }
        }

        return $result;
    }

    public static function formatKey(string $key): string
    {
        return mb_strtolower($key);
    }
}
