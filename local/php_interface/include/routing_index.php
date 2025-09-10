<?php
use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\Engine\Action;
use Bitrix\Main\Engine\AutoWire\Parameter;
use Bitrix\Main\Engine\Contract\RoutableAction;
use Bitrix\Main\Engine\Response\Json;
use Bitrix\Main\Loader;
use Bitrix\Main\Routing\Controllers\PublicPageController;
use Bitrix\Main\SystemException;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/urlrewrite.php';

$application = \Bitrix\Main\HttpApplication::getInstance();
$application->initializeExtendedKernel(array(
    "get" => $_GET,
    "post" => $_POST,
    "files" => $_FILES,
    "cookie" => $_COOKIE,
    "server" => $_SERVER,
    "env" => $_ENV
));

$router = $application->getRouter();

// match request
$request = Context::getCurrent()->getRequest();
$route = $router->match($request);

if ($route !== null) {
    $application->setCurrentRoute($route);

    // copy route parameters to the request
    if ($route->getParametersValues()) {
        foreach ($route->getParametersValues()->getValues() as $name => $value) {
            $_GET[$name] = $value;
            $_REQUEST[$name] = $value;
        }
    }

    $_SERVER["REAL_FILE_PATH"] = '/bitrix/routing_index.php';
    $controller = $route->getController();

    if ($controller instanceof PublicPageController) {
        include_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/classes/general/virtual_io.php");
        $io = CBXVirtualIo::GetInstance();

        $_SERVER["REAL_FILE_PATH"] = $controller->getPath();

        include_once($io->GetPhysicalName($_SERVER['DOCUMENT_ROOT'] . $controller->getPath()));
        die;
    } elseif ($controller instanceof \Closure) {
        $binder = Main\Engine\AutoWire\Binder::buildForFunction($controller);

        // pass current route
        $binder->appendAutoWiredParameter(new Parameter(
            Main\Routing\Route::class,
            fn () => $route
        ));

        // pass request
        $binder->appendAutoWiredParameter(new Parameter(
            Main\HttpRequest::class,
            fn () => Context::getCurrent()->getRequest()
        ));

        // pass named parameters
        $binder->setSourcesParametersToMap([
            $route->getParametersValues()->getValues()
        ]);

        // init kernel
        require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

        // call
        $result = $binder->invoke();

        // send response
        if ($result !== null) {
            if ($result instanceof Main\HttpResponse) {
                // ready response
                $response = $result;
            } elseif (is_array($result)) {
                // json
                $response = new Json($result);
            } else {
                // string
                $response = new Main\HttpResponse;
                $response->setContent($result);
            }

            $application->getContext()->setResponse($response);
            $response->send();
        }

        // terminate app
        $application->terminate(0);
    }

    if (is_array($controller)) {
        require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

        [$controllerClass, $actionName] = $controller;
        try {
            Loader::requireClass($controllerClass);
        } catch (\Bitrix\Main\LoaderException $e) {
            // Ловим исключение, но ничего не делаем, так как автозагрузчик может подключить класс
        }
        if (is_subclass_of($controllerClass, Main\Engine\Controller::class)) {
            if (substr($actionName, -6) === 'Action')
			{
				$actionName = substr($actionName, 0, -6);
			}
            $fullActionName = $actionName . 'Action';

            if (method_exists($controllerClass, $fullActionName)) {
                /** @var Main\HttpApplication $app */
                $app = Main\Application::getInstance();
                $app->runController($controllerClass, $actionName);
            } else {
                throw new SystemException(sprintf('Action %s not callable in %s', $fullActionName, $controllerClass));
            }
        } else {
            throw new SystemException(sprintf('Class %s is not a subclass of Main\Engine\Controller', $controllerClass));
        }
    } elseif (is_string($controller)) {
        require_once($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

        $actionClass = $controller;

        try {
            Loader::requireClass($actionClass);
        } catch (\Bitrix\Main\LoaderException $e) {
            // Ловим исключение, но ничего не делаем, так как автозагрузчик может подключить класс
        }

        if (is_subclass_of($controller, Action::class)) {
            if (is_subclass_of($actionClass, RoutableAction::class)) {
                /** @var RoutableAction $actionClass */
                $controllerClass = $actionClass::getControllerClass();
                $actionName = $actionClass::getDefaultName();
                if (substr($actionName, -6) === 'Action')
                {
                    $actionName = substr($actionName, 0, -6);
                }
                $fullActionName = $actionName . 'Action';

                if (method_exists($controllerClass, $fullActionName)) {
                    /** @var Main\HttpApplication $app */
                    $app = Main\Application::getInstance();
                    $app->runController($controllerClass, $actionName);
                } else {
                    throw new SystemException(sprintf('Action %s not callable in %s', $fullActionName, $controllerClass));
                }
            } else {
                throw new SystemException(sprintf(
                    'Action %s should implement %s interface for being called in routing',
                    $actionClass,
                    RoutableAction::class
                ));
            }
        } else {
            throw new SystemException(sprintf('Class %s is not a subclass of Action', $actionClass));
        }
    }

    throw new SystemException(sprintf(
        'Unknown controller `%s`',
        $controller
    ));
}


