<?php
declare(strict_types=1);

namespace Beeralex\Apiship\Client;

use Apiship\Adapter\GuzzleTokenAdapter;
use Apiship\Exception\ExceptionInterface;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Адаптер ApiShip с авторизацией по готовому токену и настраиваемой проверкой SSL.
 *
 * Расширяет GuzzleTokenAdapter, добавляя опцию verify (SSL) в Guzzle-клиент и
 * сохраняя штатные middleware (подстановка токена + обработка ответа/ошибок).
 */
class TokenAdapter extends GuzzleTokenAdapter
{
    /**
     * @param string $accessToken Токен доступа ApiShip.
     * @param bool $test Использовать тестовый контур.
     * @param bool $sslVerify Проверять SSL-сертификат.
     * @param string|null $customUrl Кастомный базовый URL API (переопределяет prod/dev).
     * @param string|null $platform Значение заголовка platform (опционально).
     */
    public function __construct(
        string $accessToken,
        bool $test = false,
        bool $sslVerify = true,
        ?string $customUrl = null,
        ?string $platform = null,
        ?ExceptionInterface $exception = null
    ) {
        $client = new Client([
            'verify'  => $sslVerify,
            'handler' => $this->buildHandlerStack(),
        ]);

        parent::__construct($accessToken, $test, $client, $exception, $platform);

        if ($customUrl) {
            $this->setCustomUrl($customUrl);
        }
    }

    /**
     * Повторяет штатные middleware SDK: авторизация и обработка ответа.
     */
    private function buildHandlerStack(): HandlerStack
    {
        $handler = HandlerStack::create();

        $handler->push(
            Middleware::mapRequest(function (RequestInterface $request) {
                return $request->withHeader('Authorization', $this->getAccessToken());
            })
        );

        $handler->push(
            Middleware::mapResponse(function (ResponseInterface $response) {
                if ($this->accessToken && $this->tokenRequested) {
                    $this->tokenRequested = false;
                }
                $this->handleResponse($response);
                return $response;
            })
        );

        return $handler;
    }
}
