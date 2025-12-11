<?php
declare(strict_types=1);
namespace Beeralex\User\Auth\Social\Services\Bitrix;

use Beeralex\User\Auth\Social\Contracts\AuthServiceContract;
use Beeralex\User\Auth\Social\Services\TelegramAuthService;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Телеграмм социальный сервис для админки
 * @property TelegramAuthService service
 */
class BitrixTelegramService extends AbstractSocServAuthService
{
    public function getId(): string
    {
        return 'telegram';
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
        return '';
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
        return '';
    }

    protected function createService(): AuthServiceContract
    {
        return new TelegramAuthService($this);
    }
}
