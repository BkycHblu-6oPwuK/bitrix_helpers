<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Sdk\Api;

use Apiship\Api\AbstractApi;
use Beeralex\Apiship\Sdk\Entity\Response\RawResponse;

/**
 * База для расширенных Api-классов модуля.
 *
 * Инкапсулирует типовой цикл: запрос через adapter -> json_decode -> RawResponse.
 * Наследует AbstractApi SDK (protected $adapter).
 */
abstract class AbstractExtendedApi extends AbstractApi
{
    /**
     * GET-запрос -> RawResponse.
     *
     * @param array<string,mixed> $query
     * @param array<string,string> $headers
     */
    protected function requestGet(string $endpoint, array $query = [], array $headers = []): RawResponse
    {
        $json = $this->adapter->get($endpoint, $headers, $query);
        return $this->toResponse($json);
    }

    /**
     * POST-запрос -> RawResponse.
     *
     * @param array<string,string> $headers
     */
    protected function requestPost(string $endpoint, mixed $body = null, array $headers = []): RawResponse
    {
        $json = $this->adapter->post($endpoint, $headers, $this->encodeBody($body));
        return $this->toResponse($json);
    }

    /**
     * PUT-запрос -> RawResponse.
     *
     * @param array<string,string> $headers
     */
    protected function requestPut(string $endpoint, mixed $body = null, array $headers = []): RawResponse
    {
        $json = $this->adapter->put($endpoint, $headers, $this->encodeBody($body));
        return $this->toResponse($json);
    }

    /**
     * DELETE-запрос -> RawResponse.
     *
     * @param array<string,string> $headers
     */
    protected function requestDelete(string $endpoint, array $headers = []): RawResponse
    {
        $json = $this->adapter->delete($endpoint, $headers);
        return $this->toResponse($json);
    }

    private function encodeBody(mixed $body): string
    {
        if ($body === null) {
            return '';
        }
        if (is_string($body)) {
            return $body;
        }
        return json_encode($body, JSON_UNESCAPED_UNICODE) ?: '';
    }

    private function toResponse(string $json): RawResponse
    {
        $response = new RawResponse();
        $response->setOriginJson($json);
        $response->fill(json_decode($json));
        return $response;
    }
}
