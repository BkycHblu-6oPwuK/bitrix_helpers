<?php
declare(strict_types=1);
namespace Beeralex\User\Auth\Social\Services;

use Beeralex\User\Auth\Social\BirixUser;
use Beeralex\User\Auth\Social\Contracts\AuthServiceContract;
use Beeralex\User\Auth\Social\Services\Bitrix\AbstractSocServAuthService;

/**
 * Реализация сервиса авторизации через Telegram.
 */
class TelegramAuthService implements AuthServiceContract
{
    /** Токен бота Telegram (используется для проверки подписи) */
    public readonly string $botToken;
    /** Имя бота Telegram */
    public readonly string $botName;
     /** Bitrix-социальный сервис */
    public readonly AbstractSocServAuthService $bitrixService;

    public function __construct(AbstractSocServAuthService $bitrixService)
    {
        $this->bitrixService = $bitrixService;
        $this->botToken = $bitrixService->GetOption('telegram_bot_token');
        $this->botName = $bitrixService->GetOption('telegram_bot_name');
    }

    public function verify(array $data): bool
    {
        if (!isset($data['hash'])) return false;

        $hash = $data['hash'];
        unset($data['hash']);
        ksort($data);
        $checkString = implode("\n", array_map(
            fn($k, $v) => "$k=$v",
            array_keys($data),
            $data
        ));

        $secretKey = hash('sha256', $this->botToken, true);
        $calc = hash_hmac('sha256', $checkString, $secretKey);

        return hash_equals($calc, $hash);
    }

    public function getUser(array $data): BirixUser
    {
        return new BirixUser(
            $data['id'],
            $data['first_name'] ?? '',
            $data['last_name'] ?? '',
            $data['username'] ?? '',
            $data['email'] ?? '',
            $data['photo_url'] ?? '',
            $data['auth_date'] ?? 0,
            $this->bitrixService->getId(),
            mb_strtolower($this->bitrixService->getLoginPrefix())
        );
    }
}
