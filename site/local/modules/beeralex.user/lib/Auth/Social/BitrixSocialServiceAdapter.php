<?php
declare(strict_types=1);
namespace Beeralex\User\Auth\Social;

use Beeralex\User\Auth\Social\Contracts\SocialServiceProviderContract;
use CSocServAuthManager;
use CSocServAuth;

class BitrixSocialServiceAdapter implements SocialServiceProviderContract
{
    public readonly CSocServAuth|\CSocServOpenID $service;
    public readonly bool $isEnable;
    protected static ?array $services = null;
    public readonly string $key;

    public function __construct(string $key, bool $isEnable = false)
    {
        $this->key = $key;
        $this->isEnable = $isEnable;
        
        if (static::$services === null) {
            $manager = new CSocServAuthManager();
            static::$services = $manager->GetAuthServices(SITE_ID);
        }

        if (!isset(static::$services[$key])) {
            throw new \InvalidArgumentException("Social auth service '{$key}' not found or inactive");
        }

        $class = static::$services[$key]['CLASS'];
        if (!class_exists($class)) {
            throw new \RuntimeException("Social auth class '{$class}' not found");
        }
        $this->service = new $class();
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function getName(): string
    {
        return static::$services[$this->key]['NAME'] ?? $this->key;
    }

    /**
     * Получаем URL для редиректа на авторизацию.
     */
    public function getAuthorizationUrlOrHtml(array $params = []): array
    {
        if (method_exists($this->service, 'getUrl')) {
            $ref = new \ReflectionMethod($this->service, 'getUrl');
            $url = match ($ref->getNumberOfParameters()) {
                0 => $this->service->getUrl(),
                1 => $this->service->getUrl($params),
                2 => $this->service->getUrl('page', $params),
                default => $this->service->getUrl('opener', null, $params),
            };
            return [
                'type' => 'url',
                'value' => $url,
            ];
        } elseif(method_exists($this->service, 'GetFormHtml')) {
            $html = $this->service->GetFormHtml($params);
            if(!empty($html)) {
                return [
                    'type' => 'html',
                    'value' => $html
                ];
            }

        }

        throw new \RuntimeException("Method getUrl() not found in {$this->key}");
    }

    /**
     * Проверяем, авторизован ли пользователь через этот сервис.
     */
    public function isAuthorized(): bool
    {
        return $this->getProfile() !== null;
    }

    /**
     * Получаем профиль текущего пользователя из Битрикса.
     */
    public function getProfile(): ?array
    {
        if (method_exists($this->service->getEntityOAuth(), 'GetCurrentUser')) {
            return $this->service->getEntityOAuth()->GetCurrentUser() ?: null;
        }
        return null;
    }

    /**
     * Запускаем процесс авторизации (Bitrix сам создаёт/логинит пользователя).
     */
    public function authorize(): bool
    {
        if (!method_exists($this->service, 'Authorize')) {
            throw new \RuntimeException("Service {$this->key} does not support Authorize()");
        }
        $this->service->Authorize(); // в битриксе это просто вызывает редирект на страницу авторизации, у нас это будет обработано в контроллере
        return $GLOBALS['AUTH_SOCSERV_RESULT'] ?? false;
    }
}
