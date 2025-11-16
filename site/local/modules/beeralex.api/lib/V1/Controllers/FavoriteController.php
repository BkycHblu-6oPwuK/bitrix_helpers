<?php

namespace Beeralex\Favorite\Controllers;

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Bitrix\Main\Error;
use Beeralex\Favorite\Helper;

class FavoriteController extends Controller
{
    public function configureActions()
    {
        return [
            'add' => [
                'prefilters' => [
                    new ActionFilter\Csrf(),
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                ],
            ],
            'delete' => [
                'prefilters' => [
                    new ActionFilter\Csrf(),
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                ],
            ],
            'toggle' => [
                'prefilters' => [
                    new ActionFilter\Csrf(),
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                ],
            ],
            'clear' => [
                'prefilters' => [
                    new ActionFilter\Csrf(),
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                ],
            ],
            'get' => [
                'prefilters' => [
                    new ActionFilter\Csrf(),
                    new ActionFilter\HttpMethod([ActionFilter\HttpMethod::METHOD_POST]),
                ],
            ],
        ];
    }

    public function addAction(int $productID)
    {
        $res = Helper::add($productID);

        if ($res) {
            return [
                'result'     => 'success',
                'totalCount' => Helper::getCountByUser(),
            ];
        }

        $this->addError(new Error('Failed to add favorite item.'));
        return null;
    }

    public function deleteAction(int $productID)
    {
        $res = Helper::deleteByProductID($productID);

        if ($res) {
            return [
                'result'     => 'success',
                'totalCount' => Helper::getCountByUser(),
            ];
        }

        $this->addError(new Error('Failed to delete favorite item.'));
        return null;
    }

    public function toggleAction(int $productID)
    {
        if (Helper::isFavoriteProduct($productID)) {
            $result = $this->deleteAction($productID);
            if ($result) {
                $result['action'] = 'delete';
            }
        } else {
            $result = $this->addAction($productID);
            if ($result) {
                $result['action'] = 'add';
            }
        }

        return $result ?? ['result' => 'error'];
    }

    public function clearAction()
    {
        $isSuccess = Helper::clear();
        return ['success' => $isSuccess];
    }

    public function getAction()
    {
        return Helper::getByUser();
    }
}
