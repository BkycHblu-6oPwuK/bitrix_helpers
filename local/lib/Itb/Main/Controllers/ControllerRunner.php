<?php

namespace Itb\Main\Controllers;

use Bitrix\Main\Context;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ControllerBuilder;
use Bitrix\Main\Engine\CurrentUser;
use Bitrix\Main\Engine\Router;
use Bitrix\Main\Error;
use Bitrix\Main\HttpRequest;
use Bitrix\Main\HttpResponse;
use Bitrix\Main\Result;
use Bitrix\Main\SystemException;

class ControllerRunner
{
    /**
     * Запускает контроллер по переданным GET/POST
     */
    public function run(array $query = [], array $post = []): Result
    {
        $context = Context::getCurrent();
        $server  = $context->getServer();

        $request = new HttpRequest(
            $server,
            $query,
            $post,
            [], // files
            $_COOKIE
        );

        $router = new Router($request);

        try {
            [$controller, $actionName] = $router->getControllerAndAction();

            if (!$controller) {
                throw new SystemException('Не удалось найти контроллер для запроса');
            }

            if (is_string($controller)) {
                $controller = ControllerBuilder::build($controller, [
                    'scope'       => Controller::SCOPE_AJAX,
                    'currentUser' => CurrentUser::get(),
                ]);
            }

            if (!$controller instanceof Controller) {
                throw new SystemException('Некорректный контроллер');
            }

            $rawResult = $controller->run($actionName, $this->getSourceParametersList($request));

            $errors = $controller->getErrors();

            $result = $this->normalizeResult($rawResult);
            foreach ($errors as $error) {
                $result->addError($error);
            }

            return $result;
        } catch (\Throwable $e) {
            $result = new Result();
            $result->addError(new Error($e->getMessage()));
            return $result;
        }
    }

    protected function normalizeResult($raw): Result
    {
        if ($raw instanceof Result) {
            return $raw;
        }

        $result = new Result();

        if ($raw instanceof HttpResponse) {
            $result->setData(['content' => $raw->getContent()]);
            return $result;
        }

        if (is_array($raw) || is_object($raw)) {
            $result->setData(['data' => $raw]);
            return $result;
        }

        if (is_scalar($raw)) {
            $result->setData(['value' => $raw]);
            return $result;
        }

        $result->setData(['raw' => $raw]);
        return $result;
    }

    protected function getSourceParametersList(HttpRequest $request): array
    {
        return [
            'get'    => $request->getQueryList()->toArray(),
            'post'   => $request->getPostList()->toArray(),
            'files'  => $request->getFileList()->toArray(),
            'cookie' => $request->getCookieList()->toArray(),
        ];
    }

    /**
     * Запускает action контроллера (модуль)
     */
    public function runAction(string $action, array $post = []): Result
    {
        return $this->run(['action' => $action], $post);
    }

    /**
     * Запускает action компонента
     */
    public function runComponent(
        string $component,
        string $action,
        ?string $signedParameters = null,
        array $post = []
    ): Result {
        $query = [
            'c'      => $component,
            'mode'   => Router::COMPONENT_MODE_CLASS,
            'action' => $action,
        ];

        if ($signedParameters) {
            $post['signedParameters'] = $signedParameters;
        }

        return $this->run($query, $post);
    }

    /**
     * Запускает action ajax-компонента
     */
    public function runAjaxComponent(string $component, string $action, array $post = []): Result
    {
        $query = [
            'c'      => $component,
            'mode'   => Router::COMPONENT_MODE_AJAX,
            'action' => $action,
        ];

        return $this->run($query, $post);
    }
}
