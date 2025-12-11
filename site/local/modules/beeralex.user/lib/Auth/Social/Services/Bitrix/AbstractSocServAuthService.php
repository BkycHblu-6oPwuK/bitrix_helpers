<?php
declare(strict_types=1);
namespace Beeralex\User\Auth\Social\Services\Bitrix;

use Beeralex\User\Auth\Social\Contracts\AuthServiceContract;
use Bitrix\Main\Web\Uri;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

/**
 * Базовая реализация авторизации в социальном сервисе по битриксовому
 */
abstract class AbstractSocServAuthService extends \CSocServAuth
{
    protected AuthServiceContract $service;

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

    /** Реальный сервис для интеграции */
    abstract protected function createService(): AuthServiceContract;

    public function Authorize(): void
    {
        global $APPLICATION;
        $APPLICATION->RestartBuffer();

        $data = $_GET;
        $bSuccess = false;

        if ($this->service->verify($data)) {
            $user = $this->service->getUser($data);

            $arFields = $this->prepareUser($user->toBitrixArray());
            $authResult = $this->AuthorizeUser($arFields);

            $bSuccess = $authResult === true;
        }

        $this->finalRedirect($bSuccess ? '' : SOCSERV_AUTHORISATION_ERROR);
    }

    protected function prepareUser(array $userData): array
    {
        return $userData;
    }

    protected function getAuthUrl(): string
    {
        $id = $this->getId();
        return (new Uri("/bitrix/services/main/ajax.php?action=beeralex:user.AuthController.callback&provider={$id}"))->toAbsolute()->__toString();
    }

    protected function finalRedirect(string|int $authError): void
    {
        global $APPLICATION;
        $url = $APPLICATION->GetCurDir() . '?auth_service_id=' . $this->getId();

        if ($authError) {
            $url .= '&auth_service_error=' . (string)$authError;
        }
        $GLOBALS['AUTH_SOCSERV_RESULT'] = $authError !== 1;
    }
}
