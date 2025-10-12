<?php

namespace App\Http\Controllers;

use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Bitrix\Main\Web\Json;

/**
 * @todo bitrix там что то делает с атрибутами, и вроде можно будет автовалидировать без обработки в processBeforeAction
 */
abstract class BaseController extends Controller
{
    protected function processBeforeAction(Action $action): bool
    {
        $request = $this->getRequest();

        $data = $request->isPost()
            ? $request->getPostList()->toArray()
            : $request->getQueryList()->toArray();

        if (empty($data) && $request->isJson()) {
            try {
                $data = Json::decode($request->getInput());
            } catch (\Throwable) {
                $this->addError(new Error('Некорректный JSON'));
                return false;
            }
        }

        $method = new \ReflectionMethod($this, $action->getName() . 'Action');
        $params = $method->getParameters();
        $arguments = [];

        foreach ($params as $param) {
            $type = $param->getType();

            if (!$type || $type->isBuiltin()) {
                continue;
            }

            $className = $type->getName();
            if (is_subclass_of($className, \App\Main\Request\AbstractRequestDto::class)) {
                $dto = $className::fromArray($data);
                if (!$dto->isValid()) {
                    foreach ($dto->getErrors() as $error) {
                        $this->addError($error);
                    }
                    return false;
                }
                $arguments[$param->getName()] = $dto;
                break;
            }
        }
        $action->setArguments($arguments);
        return parent::processBeforeAction($action);
    }
}
