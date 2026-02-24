<?php
declare(strict_types=1);
namespace Beeralex\User\Auth\Social\Services;

use Beeralex\User\Auth\Social\BirixUser;
use Beeralex\User\Auth\Social\Contracts\AuthServiceContract;
use Beeralex\User\Auth\Social\Contracts\AuthUserInterface;
use Beeralex\User\Auth\Social\Services\Bitrix\BitrixMailRuService;
use Bitrix\Main\Application;

/**
 * Реализация сервиса авторизации через Mail.ru (OAuth2).
 */
class MailRuAuthService implements AuthServiceContract
{
    /** Битрикс-социальный сервис (нужен для доступа к OAuth-сущности) */
    public readonly BitrixMailRuService $bitrixService;

    /** OAuth-сущность после успешного обмена кода на токен */
    private mixed $oauthEntity = null;

    public function __construct(BitrixMailRuService $bitrixService)
    {
        $this->bitrixService = $bitrixService;
    }

    /**
     * Проверяет подлинность OAuth-колбэка:
     * — наличие code;
     * — совпадение check_key из state с сессионным UNIQUE_KEY;
     * — успешный обмен кода на access_token.
     */
    public function verify(array $data): bool
    {
        if (empty($data['code'])) {
            return false;
        }

        $session = Application::getInstance()->getKernelSession();

        $arState = [];
        parse_str($data['state'] ?? '', $arState);

        if (
            empty($arState['check_key'])
            || empty($session['UNIQUE_KEY'])
            || $arState['check_key'] !== $session['UNIQUE_KEY']
        ) {
            return false;
        }

        unset($session['UNIQUE_KEY']);

        $redirectUri  = $this->bitrixService->getOAuthRedirectUri();
        $oauthEntity  = $this->bitrixService->getOAuthEntity($data['code']);

        if ($oauthEntity->GetAccessToken($redirectUri) === false) {
            return false;
        }

        $this->oauthEntity = $oauthEntity;
        return true;
    }

    /**
     * Возвращает объект пользователя по данным от Mail.ru OAuth.
     * Вызывать только после успешного verify().
     */
    public function getUser(array $data): AuthUserInterface
    {
        $arUser = $this->oauthEntity->GetCurrentUser();

        return new BirixUser(
            (int)($arUser['uid'] ?? 0),
            $arUser['first_name'] ?? '',
            $arUser['last_name']  ?? '',
            $arUser['nick']       ?? '',
            $arUser['email']      ?? '',
            $arUser['pic']        ?? '',
            time(),
            $this->bitrixService->getId(),
            mb_strtolower($this->bitrixService->getLoginPrefix())
        );
    }
}
