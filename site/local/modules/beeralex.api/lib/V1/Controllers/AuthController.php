<?php

declare(strict_types=1);

namespace Beeralex\Api\V1\Controllers;

use Beeralex\Api\ActionFilter\AuthFilter;
use Beeralex\Api\ApiProcessResultTrait;
use Beeralex\Api\ApiResult;
use Beeralex\Api\Domain\User\UserDTO;
use Beeralex\Api\Domain\User\UserService;
use Beeralex\Api\Options;
use Beeralex\Core\Http\Controllers\ApiController;
use Beeralex\User\Auth\AuthService;
use Beeralex\User\Auth\AuthCredentialsDto;
use Beeralex\User\UserRepository;

class AuthController extends ApiController
{
    use ApiProcessResultTrait;
    protected AuthService $authService;
    protected Options $options;

    protected function init(): void
    {
        parent::init();
        $this->authService = \service(AuthService::class);
        $this->options = \service(Options::class);
    }
    
    public function configureActions(): array
    {
        return [
            'login' => [
                'prefilters' => [],
            ],
            'loginFuser' => [
                'prefilters' => [],
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
            $metadata = [
                'ip' => $_SERVER['REMOTE_ADDR'] ?? null,
                'user_agent' => $_SERVER['HTTP_USER_AGENT'] ?? null,
            ];

            $result = $this->authService->login($credentials, $metadata);
            if (!$result->isSuccess()) {
                return $result;
            }

            $data = $result->getData();

            $this->setCookie('access', $data['accessToken'], $data['accessTokenExpired']);
            $this->setCookie('refresh', $data['refreshToken'], $data['refreshTokenExpired']);

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
            $refreshToken = service(UserService::class)->extractRefreshToken($this->getRequest());
            toFile(['1' => $this->getRequest()]);
            if (!$refreshToken) {
                throw new \Exception("No refresh token");
            }

            $result = $this->authService->refreshTokens($refreshToken);
            $data = $result->getData();

            $this->setCookie('access', $data['accessToken'], $data['accessTokenExpired']);
            $this->setCookie('refresh', $data['refreshToken'], $data['refreshTokenExpired']);

            return [];
        });
    }

    /**
     * Выход с отзывом refresh токена
     */
    public function logoutAction()
    {
        return $this->process(function () {
            $refreshToken = service(UserService::class)->extractRefreshToken($this->getRequest());

            if ($refreshToken) {
                $this->authService->logout($refreshToken, true);
            }

            $this->setCookie('access', '', time() - 3600);
            $this->setCookie('refresh', '', time() - 3600);

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

    public function meAction(): array
    {
        return $this->process(function () {
            $user = \service(UserRepository::class)->getCurrentUser();
            $result = \service(ApiResult::class);
            $result->setData([
                'user' => UserDTO::make([
                    'id' => $user->getId(),
                    'name' => $user->getName(),
                    'lastName' => $user->getLastName(),
                ])
            ]);
            return $result;
        });
    }

    private function setCookie(
        string $name,
        string $value,
        int $expires,
        string $path = '/'
    ): void {
        if (!$this->options->spaApiEnabled) return;
        global $APPLICATION;

        $APPLICATION->set_cookie(
            $name,
            $value,
            $expires,
            $path,
            false,
            true,
            true,
            false,
            true
        );
    }
}
