<?php

declare(strict_types=1);

namespace Beeralex\Api\ActionFilter;

use Beeralex\Api\Domain\User\UserService;
use Beeralex\User\Auth\JwtTokenManager;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Error;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Context;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;

/**
 * Action Filter для проверки JWT токенов в REST API контроллерах
 * 
 * Использование в контроллере:
 * 
 * ```php
 * use Beeralex\User\Auth\ActionFilter\JwtAuthFilter;
 * 
 * class MyApiController extends Controller
 * {
 *     public function configureActions()
 *     {
 *         return [
 *             'getProfile' => [
 *                 'prefilters' => [
 *                     new JwtAuthFilter(),
 *                 ],
 *             ],
 *         ];
 *     }
 * }
 * ```
 */
class JwtAuthFilter extends Base
{
    public const ERROR_INVALID_TOKEN = 'invalid_token';
    public const ERROR_TOKEN_EXPIRED = 'token_expired';
    public const ERROR_TOKEN_MISSING = 'token_missing';
    public const ERROR_JWT_DISABLED = 'jwt_disabled';

    private ?JwtTokenManager $jwtManager = null;
    private bool $optional = false;

    /**
     * @param array $params Параметры фильтра
     *   - 'optional' (bool) - токен не обязателен, но если есть - будет проверен
     */
    public function __construct(array $params = [])
    {
        parent::__construct();
        $this->optional = (bool)($params['optional'] ?? false);
    }

    public function onBeforeAction(Event $event)
    {
        try {
            $this->jwtManager = service(JwtTokenManager::class);
        } catch (\Exception $e) {
            Context::getCurrent()->getResponse()->setStatus(500);
            $this->addError(new Error('JWT service not available', self::ERROR_INVALID_TOKEN));
            return new EventResult(EventResult::ERROR, null, null, $this);
        }

        if (!$this->jwtManager->isEnabled()) {
            if (!$this->getOptional()) {
                Context::getCurrent()->getResponse()->setStatus(503);
                $this->addError(new Error('JWT authentication is disabled', self::ERROR_JWT_DISABLED));
                return new EventResult(EventResult::ERROR, null, null, $this);
            }
            return new EventResult(EventResult::SUCCESS, null, null, $this);
        }

        $token = \service(UserService::class)->extractJwtToken(Context::getCurrent()->getRequest());
        if (!$token) {
            if ($this->getOptional()) {
                return new EventResult(EventResult::SUCCESS, null, null, $this);
            }

            Context::getCurrent()->getResponse()->setStatus(401);
            $this->addError(new Error('JWT token is missing', self::ERROR_TOKEN_MISSING));
            return new EventResult(EventResult::ERROR, null, null, $this);
        }

        try {
            // Валидируем токен
            $decoded = $this->jwtManager->verifyToken($token);

            if (!$decoded->isSuccess()) {
                $this->addErrors($decoded->getErrors());
                return new EventResult(EventResult::ERROR, null, null, $this);
            }

            $decoded = $decoded->getData();

            // Проверяем, что это access токен
            if (!$this->jwtManager->isAccessToken($token)) {
                throw new \InvalidArgumentException('Invalid token type. Access token required.');
            }

            return new EventResult(EventResult::SUCCESS, null, null, $this);
        } catch (ExpiredException $e) {
            Context::getCurrent()->getResponse()->setStatus(401);
            $this->addError(new Error('Token has expired', self::ERROR_TOKEN_EXPIRED));
            return new EventResult(EventResult::ERROR, null, null, $this);
        } catch (SignatureInvalidException $e) {
            Context::getCurrent()->getResponse()->setStatus(401);
            $this->addError(new Error('Invalid token signature', self::ERROR_INVALID_TOKEN));
            return new EventResult(EventResult::ERROR, null, null, $this);
        } catch (\InvalidArgumentException $e) {
            Context::getCurrent()->getResponse()->setStatus(401);
            $this->addError(new Error($e->getMessage(), self::ERROR_INVALID_TOKEN));
            return new EventResult(EventResult::ERROR, null, null, $this);
        } catch (\Exception $e) {
            Context::getCurrent()->getResponse()->setStatus(500);
            $this->addError(new Error('Internal server error: ' . $e->getMessage(), self::ERROR_INVALID_TOKEN));
            return new EventResult(EventResult::ERROR, null, null, $this);
        }
    }

    /**
     * Проверка, является ли токен опциональным
     * 
     * @return bool
     */
    private function getOptional(): bool
    {
        return $this->optional;
    }
}
