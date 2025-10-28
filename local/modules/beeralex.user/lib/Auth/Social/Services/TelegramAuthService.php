<?php

namespace Beeralex\User\Auth\Social\Services;

use Beeralex\User\Auth\Social\BirixUser;

class TelegramAuthService extends AbstractAuthService
{
    public readonly string $botToken;
    public readonly string $botName;
    public readonly AbstractSocServAuthService $adapter;

    public function __construct(AbstractSocServAuthService $adapter)
    {
        $this->adapter = $adapter;
        $this->botToken = $adapter->GetOption('telegram_bot_token');
        $this->botName = $adapter->GetOption('telegram_bot_name');
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
            $data['photo_url'] ?? '',
            $data['auth_date'] ?? 0,
            mb_strtolower($this->adapter->getId())
        );
    }
}
