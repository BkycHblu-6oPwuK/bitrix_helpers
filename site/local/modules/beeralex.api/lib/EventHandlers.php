<?php

namespace Beeralex\Api;

use Beeralex\User\Auth\Authenticators\EmptyAuthentificator;
use Beeralex\User\Auth\JwtTokenManager;
use Bitrix\Main\Loader;

class EventHandlers
{
    public static function onPageStart()
    {
        $request = \Bitrix\Main\Context::getCurrent()->getRequest();

        static::authorizeFromJwt($request); // авторизация по JWT токену
        static::processFuserToken($request); // обработка Fuser токена, если авторизован то проверять заголовки не надо, в таблице fuser есть связь с пользователем
    }

    /**
     * Обработка fuser токена из заголовка X-Fuser-Token
     */
    private static function processFuserToken(\Bitrix\Main\HttpRequest $request): void
    {
        global $USER;
        if ($USER instanceof \CUser && $USER->IsAuthorized()) {
            return;
        }
        if (!Loader::includeModule('beeralex.user') || !Loader::includeModule('sale')) {
            return;
        }

        try {
            $headers = $request->getHeaders();
            $fuserToken = $headers->get('X-Fuser-Token');

            if (!$fuserToken) {
                return;
            }
            $fuserManager = service(\Beeralex\User\Auth\FuserTokenManager::class);
            $fuserId = $fuserManager->getFuserId($fuserToken);

            if ($fuserId > 0) {
                $_SESSION['SALE_USER_ID'] = $fuserId;
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * Авторизация пользователя по JWT токену из заголовка Authorization
     */
    private static function authorizeFromJwt(\Bitrix\Main\HttpRequest $request): void
    {
        if (!Loader::includeModule('beeralex.user')) {
            return;
        }
        try {
            $jwtManager = service(JwtTokenManager::class);

            if (!$jwtManager->isEnabled()) {
                return;
            }

            // Извлекаем токен из заголовка Authorization
            $token = static::extractJwtToken($request);
            if (!$token) {
                return;
            }

            $result = $jwtManager->verifyToken($token);
            if (!$result->isSuccess()) {
                return;
            }

            $decoded = $result->getData();

            // Проверяем, что это access токен
            if (!$jwtManager->isAccessToken($token)) {
                return;
            }

            // Авторизуем пользователя
            $userId = (int)$decoded['sub'];
            if ($userId > 0) {
                global $USER;
                if ($USER instanceof \CUser && (!$USER->IsAuthorized() || $USER->GetID() != $userId)) {
                    service(EmptyAuthentificator::class)->authorizeByUserId($userId);
                }
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * Извлечение JWT токена из запроса
     */
    private static function extractJwtToken(\Bitrix\Main\HttpRequest $request): ?string
    {
        $authHeader = static::getAuthorizationHeader();
        if ($authHeader && preg_match('/Bearer\s+(\S+)/', $authHeader, $matches)) {
            return $matches[1];
        }

        $token = $request->get('access_token') ?? $request->get('token');
        if ($token) {
            return $token;
        }

        return null;
    }

    /**
     * Получение заголовка Authorization
     */
    private static function getAuthorizationHeader(): ?string
    {
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return $_SERVER['HTTP_AUTHORIZATION'];
        }

        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) {
                return $headers['Authorization'];
            }
        }

        if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            return $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
        }

        return null;
    }
}
