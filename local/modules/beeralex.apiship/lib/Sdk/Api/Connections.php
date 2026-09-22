<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Sdk\Api;

use Beeralex\Apiship\Sdk\Entity\Response\RawResponse;

/**
 * Подключения к службам доставки (connections).
 *
 * OpenAPI: /connections (GET, POST), /connections/{id} (GET, PUT, DELETE),
 *          /connections/schemas (GET).
 */
class Connections extends AbstractExtendedApi
{
    /**
     * Постраничный список подключений пользователя.
     *
     * @param array<string,mixed> $filter JSON-фильтр (будет сериализован).
     */
    public function list(int $offset = 0, int $limit = 10, array $filter = []): RawResponse
    {
        $query = ['offset' => $offset, 'limit' => $limit];
        if ($filter) {
            $query['filter'] = json_encode($filter, JSON_UNESCAPED_UNICODE);
        }
        return $this->requestGet('connections', $query);
    }

    /**
     * Получение подключения по id.
     */
    public function get(int $id): RawResponse
    {
        return $this->requestGet('connections/' . $id);
    }

    /**
     * Создание подключения к службе доставки.
     *
     * @param array<string,mixed> $data Тело запроса (providerKey, name, connectParams и т.д.).
     */
    public function create(array $data): RawResponse
    {
        return $this->requestPost('connections', $data);
    }

    /**
     * Обновление подключения.
     *
     * @param array<string,mixed> $data
     */
    public function update(int $id, array $data): RawResponse
    {
        return $this->requestPut('connections/' . $id, $data);
    }

    /**
     * Удаление (пометка удалённым) подключения.
     */
    public function delete(int $id): RawResponse
    {
        return $this->requestDelete('connections/' . $id);
    }

    /**
     * Схемы подключений служб доставки (набор полей connectParams).
     */
    public function schemas(int $offset = 0, int $limit = 100, string $providerKey = ''): RawResponse
    {
        $query = ['offset' => $offset, 'limit' => $limit];
        if ($providerKey !== '') {
            $query['providerKey'] = $providerKey;
        }
        return $this->requestGet('connections/schemas', $query);
    }
}
