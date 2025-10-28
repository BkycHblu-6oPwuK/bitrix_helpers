<?php
namespace Beeralex\User\Auth\Social\Services;

use Beeralex\User\Auth\Social\Services\AbstractAuthService;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Абстрактный адаптер для соцсетей под модель Bitrix CSocServAuth.
 * Отвечает за интеграцию современных сервисов (через фабрику) с битриксовыми событиями.
 */
abstract class AbstractSocServAuthService extends \CSocServAuth
{
    protected AbstractAuthService $service;

    public function __construct()
    {
        $this->service = $this->createService();
    }

    /** Уникальный идентификатор сервиса в Bitrix (например, Telegram, VK, Google) */
    abstract public function getId(): string;

    /** Префикс для логина (например, tg, vk, gg) */
    abstract public function getLoginPrefix(): string;

    /** Имя в админке */
    abstract public function getName(): string;

    /** иконка в админке */
    abstract public function getIcon(): string;

    abstract protected function createService() : AbstractAuthService;

    public function GetSettings(): array
    {
        return [
            ['note' => Loc::getMessage('SOCSERV_ADAPTER_NOTE')],
        ];
    }

    public function Authorize(): void
    {
        global $APPLICATION;
        $APPLICATION->RestartBuffer();

        $data = $_GET;
        $bSuccess = false;

        if ($this->service && $this->service->verify($data)) {
            $user = $this->service->getUser($data);

            $arFields = $this->prepareUser($user->toBitrixArray());
            $authResult = $this->AuthorizeUser($arFields);

            $bSuccess = $authResult === true;
        }

        $this->finalRedirect($bSuccess ? '' : SOCSERV_AUTHORISATION_ERROR);
    }

    protected function prepareUser(array $userData): array
    {
        $userData['EXTERNAL_AUTH_ID'] = $this->getId();
        $userData['LOGIN'] = $this->getLoginPrefix() . '_' . ($userData['LOGIN'] ?? $userData['ID'] ?? '');
        $userData['SITE_ID'] = SITE_ID;

        return $userData;
    }

    protected function getAuthUrl(): string
    {
        $id = $this->getId();
        return (new Uri("/bitrix/services/main/ajax.php?action=beeralex:user.AuthController.callback&provider={$id}"))->toAbsolute();
    }

    protected function finalRedirect(string $authError): void
    {
        global $APPLICATION;
        $url = $APPLICATION->GetCurDir() . '?auth_service_id=' . $this->getId();

        if ($authError) {
            $url .= '&auth_service_error=' . $authError;
        }

        echo '<script>window.close(); if (window.opener) window.opener.location.reload();</script>';
        \CMain::FinalActions();
    }
}
