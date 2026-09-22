<?php
declare(strict_types=1);

namespace Beeralex\Apiship;

use Beeralex\Core\Config\AbstractOptions;

/**
 * Настройки модуля beeralex.apiship.
 *
 * Секрет (токен доступа к API) берётся из .env (APISHIP_TOKEN), чтобы не хранить его в БД.
 * Остальные параметры — из настроек модуля (options.php).
 */
final class Options extends AbstractOptions
{
    /** Токен доступа к API ApiShip (из .env APISHIP_TOKEN). */
    public readonly string $token;

    /** Использовать тестовый контур ApiShip (api.dev.apiship.ru). */
    public readonly bool $isTest;

    /** Включить логирование запросов/ответов. */
    public readonly bool $logsEnable;

    /** Проверять SSL-сертификат при запросах. */
    public readonly bool $sslVerify;

    /** ID подключения к службе доставки в ApiShip по умолчанию (providerConnectId). */
    public readonly int $defaultProviderConnectId;

    /**
     * Статус заказа Bitrix, при переходе в который заказ отправляется в ApiShip.
     * Пусто — автоотправка отключена (заказ создаётся вручную через API модуля).
     */
    public readonly string $sendOnStatusId;

    /** Кастомный базовый URL API (переопределяет prod/dev). Обычно пусто. */
    public readonly string $customApiUrl;

    protected function mapOptions(array $options): void
    {
        $this->token = (string)($_ENV['APISHIP_TOKEN'] ?? '');
        $this->isTest = ($options['is_test'] ?? '') === 'Y';
        $this->logsEnable = ($options['logs_enable'] ?? '') === 'Y';
        $this->sslVerify = ($options['ssl_verify'] ?? 'Y') === 'Y';
        $this->defaultProviderConnectId = (int)($options['default_provider_connect_id'] ?? 0);
        $this->sendOnStatusId = (string)($options['send_on_status_id'] ?? '');
        $this->customApiUrl = (string)($options['custom_api_url'] ?? '');
    }

    protected function validateOptions(): void
    {
        if ($this->token === '') {
            throw new \RuntimeException(
                'Не задан токен доступа ApiShip. Укажите переменную APISHIP_TOKEN в .env.'
            );
        }
    }

    public function getModuleId(): string
    {
        return 'beeralex.apiship';
    }
}
