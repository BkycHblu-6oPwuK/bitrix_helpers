<?php

namespace Beeralex\User\Controllers;

use Beeralex\User\Auth\AuthCredentialsDto;
use Beeralex\User\Auth\AuthService;
use Beeralex\User\User;
use Beeralex\User\UserRepository;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Throwable;

/**
 * Контроллер для авторизации через социальные сети Bitrix.
 */
class AuthController extends Controller
{
    protected AuthService $authService;

    public function __construct($request = null)
    {
        parent::__construct($request);
        $this->authService = \service(AuthService::class);
    }

    protected function getDefaultPreFilters()
    {
        return [];
    }

    public function callbackAction(string $provider)
    {
        try {
            $result = $this->authService->login(AuthCredentialsDto::make([
                'type' => $provider,
            ]));
            if (!$result->isSuccess()) {
                $this->addErrors($result->getErrors());
                return [];
            }
            $resultData = $result->getData();
            $userId = $resultData['userId'] ?? null;
            $authType = $resultData['authType'] ?? null;
            $accessToken = $resultData['accessToken'] ?? null;
            $refreshToken = $resultData['refreshToken'] ?? null;
            $accessTokenExpired = $resultData['accessTokenExpired'] ?? null;
            $refreshTokenExpired = $resultData['refreshTokenExpired'] ?? null;
            return [
                'userId' => $userId,
                'authType' => $authType,
                'accessToken' => $accessToken,
                'refreshToken' => $refreshToken,
                'accessTokenExpired' => $accessTokenExpired,
                'refreshTokenExpired' => $refreshTokenExpired,
            ];
        } catch (Throwable $e) {
            $this->addError(Error::createFromThrowable($e));
        }
    }
}