<?php
declare(strict_types=1);
namespace Beeralex\User\Auth;

use Beeralex\User\Auth\Authenticators\SocialServiceAuthenticator;
use Beeralex\User\Auth\Authenticators\SocialServiceAuthenticatorFactory;
use Beeralex\User\Auth\Social\SocialManager;

/**
 * Фабрика для создания всех активных соц. аутентификаторов.
 */
class SocialAuthenticatorFactory
{
    public function __construct(
        protected SocialManager $socialManager,
        protected SocialServiceAuthenticatorFactory $factory
    ){}

    /**
     * Возвращает список всех активных соц. аутентификаторов.
     *
     * @return SocialServiceAuthenticator[]
     */
    public function makeAll(): array
    {
        $adapters = $this->socialManager->adapters;
        $result = [];
        foreach ($adapters as $key => $adapter) {
            if(!$adapter->isEnable) continue;
            try {
                $authenticator = $this->factory->create($adapter);
                $result[$authenticator->getKey()] = $authenticator;
            } catch (\Throwable $e) {
                // можно залогировать ошибку, но не падать  
            }
        }

        return $result;
    }
}
