<?php
declare(strict_types=1);
namespace Beeralex\User\Auth\Social\Services\Bitrix;

use Beeralex\User\Auth\Social\Services\MailRuAuthService;

/**
 * Mail.ru социальный сервис для админки Битрикса.
 *
 * Наследует \CSocServMailRu2 (а не AbstractSocServAuthService), потому что
 * нужен доступ к OAuth-методам: getEntityOAuth(), GetRedirectURI() и т.д.
 * Следует тем же структурным паттернам модуля: инъекция сервиса, prepareUser.
 * Исправляет некоторые недочеты оригинальной реализации (например, проверку check_key).
 *
 * @property MailRuAuthService $service
 */
class BitrixMailRuService extends \CSocServMailRu2
{
    protected MailRuAuthService $service;

    public function __construct()
    {
        $this->service = new MailRuAuthService($this);
    }

    public function getId(): string
    {
        return static::ID;
    }

    public function getLoginPrefix(): string
    {
        return 'mr';
    }

    // -------------------------------------------------------------------------
    // Прокси к OAuth (используются из MailRuAuthService)
    // -------------------------------------------------------------------------

    /**
     * Возвращает OAuth-сущность Mail.ru с обменянным токеном.
     */
    public function getOAuthEntity(string $code): mixed
    {
        return $this->getEntityOAuth($code);
    }

    /**
     * Возвращает URI редиректа текущего сайта для OAuth.
     */
    public function getOAuthRedirectUri(): string
    {
        return (string)$this->getEntityOAuth()->GetRedirectURI();
    }

    // -------------------------------------------------------------------------
    // Битриксовый интерфейс социального сервиса
    // -------------------------------------------------------------------------

    /**
     * Возвращает URL для инициирования OAuth-авторизации (редирект на Mail.ru).
     */
    public function getUrl($arParams): string
    {
        global $APPLICATION;

        $backUrl = (string)(
            $arParams['BACKURL']
            ?? $APPLICATION->GetCurPageParam('', [
                'logout', 'auth_service_error', 'auth_service_id', 'backurl',
            ])
        );

        \CSocServAuthManager::SetUniqueKey();

        $state = http_build_query([
            'site_id'      => SITE_ID,
            'check_key'    => \CSocServAuthManager::getUniqueKey(),
            'redirect_url' => $backUrl,
        ]);

        return (string)$this->getEntityOAuth()->GetAuthUrl(
            $this->getEntityOAuth()->GetRedirectURI(),
            $state
        );
    }

    /**
     * Обрабатывает OAuth-колбэк: верифицирует данные, получает пользователя,
     * авторизует/регистрирует его в Битриксе.
     */
    public function Authorize(): void
    {
        global $APPLICATION;
        $APPLICATION->RestartBuffer();

        $authError = SOCSERV_AUTHORISATION_ERROR;
        $data = $_REQUEST;

        if ($this->service->verify($data)) {
            $user = $this->service->getUser($data);
            $authError = $this->AuthorizeUser(
                $this->prepareUser($user->toBitrixArray())
            );
        }

        $url = $this->getAuthorizeRedirectUrl($authError);
        ?>
        <script>
            if (window.opener)
                window.opener.location = '<?= \CUtil::JSEscape($url) ?>';
            window.close();
        </script>
        <?php
        \CMain::FinalActions();
    }

    /**
     * Хук для дополнительной обработки полей пользователя перед авторизацией.
     */
    public function prepareUser($userData, $short = false)
    {
        return $userData;
    }

    // -------------------------------------------------------------------------
    // Вспомогательные методы редиректа (перенесены из оригинального обработчика)
    // -------------------------------------------------------------------------

    private function getRequestState(?string $state = null): ?array
    {
        if (empty($state)) {
            $state = $_REQUEST['state'] ?? null;
        }

        if (empty($state)) {
            return null;
        }

        $arState = [];
        parse_str($state, $arState);

        return !empty($arState) ? $arState : null;
    }

    private function getAuthorizeRedirectUrl(mixed $authError): string
    {
        global $APPLICATION;

        $bSuccess = $authError === true;
        $url      = $APPLICATION->GetCurDir();

        if ($url === '/login/') {
            $url = '';
        }

        $aRemove = [
            'logout', 'auth_service_error', 'auth_service_id',
            'code', 'error_reason', 'error', 'error_description',
            'check_key', 'current_fieldset', 'state',
        ];

        $arState = $this->getRequestState();
        $urlPath = null;

        if ($bSuccess && (isset($arState['backurl']) || isset($arState['redirect_url']))) {
            $url = !empty($arState['redirect_url']) ? $arState['redirect_url'] : $arState['backurl'];

            if (mb_substr($url, 0, 1) !== '#') {
                $parseUrl    = parse_url($url);
                $urlPath     = $parseUrl['path'] ?? '/';
                $arUrlQuery  = array_filter(explode('&', $parseUrl['query'] ?? ''));

                foreach ($arUrlQuery as $key => $value) {
                    foreach ($aRemove as $param) {
                        if (mb_strpos($value, $param . '=') === 0) {
                            unset($arUrlQuery[$key]);
                            break;
                        }
                    }
                }

                $url = !empty($arUrlQuery)
                    ? $urlPath . '?' . implode('&', $arUrlQuery)
                    : $urlPath;
            }
        }

        if ($authError === SOCSERV_REGISTRATION_DENY) {
            $url .= (str_contains($url, '?') ? '&' : '?');
            $url .= 'auth_service_id=' . static::ID . '&auth_service_error=' . $authError;
        } elseif (!$bSuccess) {
            $url = $urlPath !== null
                ? $urlPath . '?auth_service_id=' . static::ID . '&auth_service_error=' . $authError
                : $APPLICATION->GetCurPageParam(
                    'auth_service_id=' . static::ID . '&auth_service_error=' . $authError,
                    $aRemove
                );
        }

        if (\CModule::IncludeModule('socialnetwork') && !str_contains($url, 'current_fieldset=')) {
            $url .= (str_contains($url, '?') ? '&' : '?') . 'current_fieldset=SOCSERV';
        }

        return $url;
    }
}
