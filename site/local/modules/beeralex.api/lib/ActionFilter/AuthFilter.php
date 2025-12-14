<?

declare(strict_types=1);

namespace Beeralex\Api\ActionFilter;

use Beeralex\Api\Domain\User\UserService;
use Beeralex\Api\Options as ApiOptions;
use Beeralex\User\Options;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;

/**
 * Проверит JWT токен, если аутентификация по нему включена, либо проверит CSRF токен и авторизацию по сессии Bitrix
 */
class AuthFilter extends Base
{
    protected readonly bool $isUserModuleLoaded;
    protected readonly array $params;

    /**
     * @param array $params Параметры фильтра
     *   - 'enabled' (bool) - включен ли CSRF фильтр (по умолчанию true)
     *   - 'tokenName' (string) - имя заголовка или параметра запроса с CSRF токеном (по умолчанию 'sessid')
     *   - 'returnNew' (bool) - возвращать ли новый токен (по умолчанию true)
     *   - 'optional' (bool) - токен JWT не обязателен, но если есть - будет проверен
     */
    public function __construct(array $params = [])
    {
        $this->isUserModuleLoaded = Loader::includeModule('beeralex.user');
        $this->params = $params;
        parent::__construct();
    }

    protected function getJwtAuthFilter(): ?JwtAuthFilter
    {
        if ($this->isUserModuleLoaded) {
            return new JwtAuthFilter($this->params);
        }

        return null;
    }

    protected function getCsrfFilter(): Csrf
    {
        return new Csrf(
            $this->params['enabled'] ?? true,
            $this->params['tokenName'] ?? 'sessid',
            $this->params['returnNew'] ?? true
        );
    }

    protected function getAuthenticationFilter(): Authentication
    {
        return new Authentication();
    }

    public function onBeforeAction(Event $event)
    {
        $jwtFilter = $this->getJwtAuthFilter();
        if ($jwtFilter !== null) {
            $optionsUser = service(Options::class);
            $optionsApi = service(ApiOptions::class);
            if ($optionsUser->enableJwtAuth && $optionsApi->spaApiEnabled) {
                return $jwtFilter->onBeforeAction($event);
            }
        }

        $authFilter = $this->getAuthenticationFilter();
        $authResult = $authFilter->onBeforeAction($event);

        if ($authResult?->getType() === EventResult::ERROR) {
            return $authResult;
        }

        $csrfFilter = $this->getCsrfFilter();
        return $csrfFilter->onBeforeAction($event);
    }
}
