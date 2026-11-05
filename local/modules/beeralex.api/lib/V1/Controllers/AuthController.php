<?php

declare(strict_types=1);

namespace Beeralex\Api\V1\Controllers;

use Itb\Altcha\ActionFilter\AltchaFilter;
use Beeralex\User\Auth\ActionFilter\AuthFilter;
use Beeralex\Api\ApiProcessResultTrait;
use Beeralex\Api\ApiResult;
use Beeralex\Api\Options;
use Beeralex\Core\Http\Controllers\ApiController;
use Beeralex\User\Auth\AuthService;
use Beeralex\User\Auth\AuthCredentialsDto;
use Beeralex\User\Auth\JwtTokenContextService;
use Bitrix\Main\Error;

class AuthController extends ApiController
{
    use ApiProcessResultTrait;
    protected AuthService $authService;
    protected JwtTokenContextService $jwtTokenContextService;
    protected Options $options;

    protected function init(): void
    {
        parent::init();
        $this->authService = \service(AuthService::class);
        $this->options = \service(Options::class);
        $this->jwtTokenContextService = \service(JwtTokenContextService::class);
    }

    public function configureActions(): array
    {
        return [
            'login' => [
                'prefilters' => [
                    new AltchaFilter()
                ],
            ],
            'refresh' => [
                'prefilters' => [],
            ],
            'register' => [
                'prefilters' => [],
            ],
            'logout' => [
                'prefilters' => [
                    new AuthFilter(),
                ],
            ],
            'methods' => [
                'prefilters' => [],
            ],
        ];
    }

    /**
     * Логин пользователя с выдачей JWT токенов
     * 
     * Body: {
     *   "type": "email",
     *   "email": "user@example.com",
     *   "password": "password"
     * }
     * 
     * @param AuthCredentialsDto $credentials DTO с данными запроса
     * @return array
     */
    public function loginAction(AuthCredentialsDto $credentials)
    {
        return $this->process(function () use ($credentials) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? null;

            if ($ip && str_contains($ip, ',')) {
                $ip = trim(explode(',', $ip)[0]);
            }

            $metadata = [
                'ip' => $ip,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ];

            $result = $this->authService->login($credentials, $metadata);
            if (!$result->isSuccess()) {
                return $result;
            }

            $data = $result->getData();

            $this->jwtTokenContextService->setAccessTokenCookie($data['accessToken'], $data['accessTokenExpired']);
            $this->jwtTokenContextService->setRefreshTokenCookie($data['refreshToken'], $data['refreshTokenExpired']);

            unset($data['accessToken'], $data['refreshToken'], $data['accessTokenExpired'], $data['refreshTokenExpired']);

            return $data;
        });
    }


    /**
     * Регистрация нового пользователя
     * 
     * Body: {
     *   "type": "email",
     *   "email": "user@example.com",
     *   "password": "password",
     *   "name": "John Doe"
     * }
     * 
     * @param AuthCredentialsDto $credentials DTO с данными запроса
     * @return array
     */
    public function registerAction(AuthCredentialsDto $credentials): array
    {
        return $this->process(function () use ($credentials) {
            return $this->authService->register($credentials);
        });
    }

    /**
     * Обновление пары токенов по refresh токену
     */
    public function refreshAction()
    {
        return $this->process(function () {
            $refreshToken = $this->jwtTokenContextService->extractRefreshToken($this->getRequest());
            $apiResult = service(ApiResult::class);
            if (!$refreshToken) {
                $apiResult->addError(new Error('Refresh token is missing', 'refresh_token_missing'));
                return $apiResult;
            }

            $result = $this->authService->refreshTokens($refreshToken);
            if (!$result->isSuccess()) {
                return $result;
            }
            $data = $result->getData();

            $this->jwtTokenContextService->setAccessTokenCookie($data['accessToken'], $data['accessTokenExpired']);
            $this->jwtTokenContextService->setRefreshTokenCookie($data['refreshToken'], $data['refreshTokenExpired']);

            return $apiResult;
        });
    }

    /**
     * Выход с отзывом refresh токена
     */
    public function logoutAction()
    {
        return $this->process(function () {
            $refreshToken = $this->jwtTokenContextService->extractRefreshToken($this->getRequest());

            if ($refreshToken) {
                $this->authService->logout($refreshToken, true);
            }

            $this->jwtTokenContextService->setAccessTokenCookie('', time() - 3600);
            $this->jwtTokenContextService->setRefreshTokenCookie('', time() - 3600);

            return [];
        });
    }

    /**
     * Получение списка доступных методов аутентификации
     */
    public function methodsAction(): array
    {
        return $this->process(function () {
            return $this->authService->getAvailableAuthMethods();
        });
    }
}
