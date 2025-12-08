<?php

declare(strict_types=1);

namespace Beeralex\User\Auth;

use Beeralex\User\Options;
use Bitrix\Main\Result;
use Bitrix\Main\Web\Json;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * Менеджер для работы с JWT токенами анонимных пользователей (FUser)
 * Используется для безопасной передачи FuserId между запросами
 */
class FuserTokenManager
{
    private const TOKEN_TYPE = 'fuser';
    private const DEFAULT_TTL = 86400 * 365; // 1 год

    public function __construct(
        protected readonly Options $options
    ) {}

    /**
     * Генерация JWT токена для FuserId
     *
     * @param int $fuserId FUser ID из модуля Sale
     * @return Result<array{fuserToken: string}>
     */
    public function generateToken(int $fuserId): Result
    {
        $result = new Result();

        if (empty($this->options->jwtSecretKey)) {
            $result->addError(new \Bitrix\Main\Error('JWT secret key is not configured', 'fuser_token'));
            return $result;
        }

        if ($fuserId <= 0) {
            $result->addError(new \Bitrix\Main\Error('Invalid fuserId', 'fuser_token'));
            return $result;
        }

        $now = time();
        $payload = [
            'iss' => $this->options->jwtIssuer,
            'iat' => $now,
            'exp' => $now + self::DEFAULT_TTL,
            'sub' => (string)$fuserId,
            'type' => self::TOKEN_TYPE,
            'jti' => $this->generateJti(),
        ];

        try {
            $token = JWT::encode($payload, $this->options->jwtSecretKey, $this->options->jwtAlgorithm);
            $result->setData(['fuserToken' => $token]);
        } catch (\Exception $e) {
            $result->addError(new \Bitrix\Main\Error('Failed to generate token: ' . $e->getMessage(), 'fuser_token'));
        }

        return $result;
    }

    /**
     * Валидация и извлечение FuserId из токена
     *
     * @param string $token JWT токен
     * @return Result<array{fuserId: int}>
     */
    public function verifyToken(string $token): Result
    {
        $result = new Result();

        try {
            $decoded = JWT::decode($token, new Key($this->options->jwtSecretKey, $this->options->jwtAlgorithm));
            $payload = (array)$decoded;

            if (($payload['type'] ?? null) !== self::TOKEN_TYPE) {
                $result->addError(new \Bitrix\Main\Error('Invalid token type'));
                return $result;
            }

            $result->setData(['fuserId' => (int)$payload['sub']]);
            return $result;
        } catch (\Firebase\JWT\ExpiredException $e) {

            $payload = $this->decodePayloadUnsafe($token);

            if ($payload && ($payload['type'] ?? null) === self::TOKEN_TYPE) {
                $result->addError(new \Bitrix\Main\Error('expired', 'expired'));
                $result->setData(['fuserId' => (int)($payload['sub'] ?? 0)]);
                return $result;
            }

            $result->addError(new \Bitrix\Main\Error('Token expired'));
            return $result;
        } catch (\Exception $e) {
            $result->addError(new \Bitrix\Main\Error('Invalid token'));
            return $result;
        }
    }


    /**
     * Извлечение FuserId из токена (упрощенный метод)
     *
     * @param string $token JWT токен
     * @return int FuserId или 0 если токен невалиден
     */
    public function getFuserId(string $token): int
    {
        $result = $this->verifyToken($token);
        if (!$result->isSuccess()) {
            return 0;
        }

        return $result->getData()['fuserId'] ?? 0;
    }

    /**
     * Проверка валидности токена
     *
     * @param string $token JWT токен
     * @return bool
     */
    public function isValid(string $token): bool
    {
        return $this->verifyToken($token)->isSuccess();
    }

    /**
     * Генерация уникального идентификатора токена
     *
     * @return string
     */
    private function generateJti(): string
    {
        return bin2hex(random_bytes(16));
    }

    /**
     * Декодирование payload JWT без проверки exp/nbf/signature
     * для возможности рефреша.
     */
    private function decodePayloadUnsafe(string $token): ?array
    {
        // JWT формат: header.payload.signature
        $parts = explode('.', $token);
        if (count($parts) !== 3) {
            return null;
        }

        $payload = Json::decode(base64_decode(strtr($parts[1], '-_', '+/')));
        return is_array($payload) ? $payload : null;
    }
}
