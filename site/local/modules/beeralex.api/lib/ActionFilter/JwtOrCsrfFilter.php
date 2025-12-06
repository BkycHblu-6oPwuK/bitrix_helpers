<?

declare(strict_types=1);

namespace Beeralex\Api\ActionFilter;

use Beeralex\User\Auth\ActionFilter\JwtAuthFilter;
use Beeralex\User\Options;
use Bitrix\Main\Engine\ActionFilter\Base;
use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Event;
use Bitrix\Main\EventResult;
use Bitrix\Main\Loader;

class JwtOrCsrfFilter extends Base
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

    public function onBeforeAction(Event $event)
    {
        $jwtFilter = $this->getJwtAuthFilter();
        $csrfFilter = $this->getCsrfFilter();

        if ($jwtFilter !== null) {
            $options = service(Options::class);
            if (!$options->enableJwtAuth) {
                $jwtResult = $jwtFilter->onBeforeAction($event);
                if ($jwtResult?->getType() === EventResult::SUCCESS) {
                    return $jwtResult;
                }
            }
        }

        return $csrfFilter->onBeforeAction($event);
    }
}
