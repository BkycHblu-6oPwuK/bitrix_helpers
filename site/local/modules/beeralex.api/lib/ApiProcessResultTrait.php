<?php
namespace Beeralex\Api;

use Beeralex\Api\Enum\Errors;
use Bitrix\Main\Error;
use Bitrix\Main\Result;

/**
 * Трейт для обработки результатов в API контроллерах
 */
trait ApiProcessResultTrait
{
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
            \AddMessage2Log($e->getMessage(), 'beeralex.api');
            $this->addError(new Error('Internal Server Error', Errors::INTERNAL_ERROR->value));
            return [];
        }
    }
}
