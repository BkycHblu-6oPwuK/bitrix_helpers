<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Sdk\Api;

use Beeralex\Apiship\Sdk\Entity\Response\RawResponse;

/**
 * Подписки на вебхуки ApiShip.
 *
 * @link https://docs.apiship.ru/docs/api/webhooks
 * OpenAPI: POST/GET /webhooks, DELETE /webhooks/{uuid}
 */
class Webhooks extends AbstractExtendedApi
{
    /**
     * Подписка на событие.
     *
     * @param string $url URL, на который ApiShip будет слать события.
     * @param string $type Тип события (см. документацию, напр. "orderStatus").
     * @param array<string,mixed> $extra Доп. поля тела запроса.
     */
    public function subscribe(string $url, string $type, array $extra = []): RawResponse
    {
        $body = array_merge(['url' => $url, 'type' => $type], $extra);
        return $this->requestPost('webhooks', $body);
    }

    /**
     * Список зарегистрированных подписок.
     */
    public function list(): RawResponse
    {
        return $this->requestGet('webhooks');
    }

    /**
     * Удаление подписки по uuid.
     */
    public function delete(string $uuid): RawResponse
    {
        return $this->requestDelete('webhooks/' . rawurlencode($uuid));
    }
}
