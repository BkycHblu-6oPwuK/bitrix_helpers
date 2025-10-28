<?php

namespace Beeralex\User\Auth\Social\Services;

use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * @property TelegramAuthService service
 */
class BitrixTelegramService extends AbstractSocServAuthService
{
    public function getId(): string
    {
        return 'Telegram';
    }

    public function getLoginPrefix(): string
    {
        return 'tg';
    }

    public function getName(): string
    {
        return 'Телеграм';
    }

    public function getIcon(): string
    {
        return '1';
    }

    public function GetSettings(): array
    {
        return [
            ['telegram_bot_token', 'telegram bot token', '', ['text', 40]],
            ['telegram_bot_name', 'telegram bot name', '', ['text', 40]],
            ['note' => 'телеграм'],
        ];
    }

    public function GetFormHtml($arParams)
    {
        $authUrl = $this->getAuthUrl();
        $botName = htmlspecialcharsbx($this->service->botName);
        $phrase = Loc::getMessage('SOCSERV_TELEGRAM_FORM_NOTE');

        return <<<HTML
<script async src="https://telegram.org/js/telegram-widget.js?7"
        data-telegram-login="{$botName}"
        data-size="large"
        data-userpic="false"
        data-auth-url="{$authUrl}"
        data-request-access="write">
</script>
<span>{$phrase}</span>
HTML;
    }

    public function GetOnClickJs($arParams)
    {
        return ''; // Telegram использует встроенный виджет
    }

    protected function createService(): AbstractAuthService
    {
        return new TelegramAuthService($this);
    }
}
