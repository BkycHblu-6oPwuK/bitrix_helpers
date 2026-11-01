<?php

namespace Beeralex\Api;

use Beeralex\Api\Enum\Errors;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Response;
use Bitrix\Main\Result;
use Shestpa\LastModified\LastModifiedAjaxHelper;
use Bitrix\Main\Engine\Response\AjaxJson;

/**
 * Трейт для обработки результатов в API контроллерах
 */
trait ApiProcessResultTrait
{
    private bool $forceSetCookiesByGet = false;
    private bool $disabledSetLastModified = false;

    /**
     * Включает принудительную установку куки даже для GET-запросов
     */
    protected function enableForceSetCookiesByGet(): void
    {
        $this->forceSetCookiesByGet = true;
    }

    /**
     * Отключает установку Last-Modified заголовка для текущего ответа
     */
    protected function disableSetLastModified(): void
    {
        $this->disabledSetLastModified = true;
    }

    /**
     * апи в гет запросах не ставит куки
     * Если запрос предполагает кеширование, то передача сессии выдаст персональные данные всем пользователям
     * @param AjaxJson $response
     */
    public function finalizeResponse(Response $response)
    {
        parent::finalizeResponse($response);

        if (headers_sent()) {
            return;
        }

        $this->setLastModified($response);

        if ($this->request->getRequestMethod() === 'GET' && !$this->forceSetCookiesByGet) {
            header_remove('Set-Cookie');
            header_remove('Cache-Control');
            header_remove('Expires');
            header_remove('Pragma');
        }
    }

    /**
     * @param AjaxJson $response
     */
    protected function setLastModified(Response $response)
    {
        if (!$this->disabledSetLastModified && Loader::includeModule('shestpa.lastmodified')) {
            $content = $response->getContent();
            LastModifiedAjaxHelper::onEndBufferContent($content, $response);
        }
    }

    /**
     * Обрабатывает результат выполнения колбэка, добавляя ошибки в контроллер при необходимости
     * @param callable $callback Функция, возвращающая Result или массив данных
     */
    protected function process(callable $callback): array
    {
        try {
            $result = $callback();
            if ($result instanceof Result && !$result->isSuccess()) {
                $this->addErrors($result->getErrors());
                return [];
            }
            return $result instanceof Result ? $result->getData() : $result;
        } catch (\Throwable $e) {
            log(print_r([
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ], true) . "\n");
            $this->addError(new Error('Internal Server Error', Errors::INTERNAL_ERROR->value));
            return [];
        }
    }
}
