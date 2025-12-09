<?php

namespace Beeralex\Api\V1\Controllers;

use Beeralex\Api\ApiProcessResultTrait;
use Beeralex\Api\ApiResult;
use Beeralex\Core\Service\FileService;
use Beeralex\Favorite\FavouriteService;
use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Error;
use Bitrix\Main\Loader;
use Bitrix\Main\Request;

class FavoriteController extends Controller
{
    use ApiProcessResultTrait;

    protected readonly FavouriteService $favouriteService;

    public function __construct(?Request $request = null)
    {
        Loader::requireModule('beeralex.favorite');
        $this->favouriteService = service(FavouriteService::class);
        parent::__construct($request);
    }

    public function configureActions()
    {
        return [
            'store' => [
                'prefilters' => [],
            ],
            'delete' => [
                'prefilters' => [],
            ],
            'toggle' => [
                'prefilters' => [],
            ],
            'clear' => [
                'prefilters' => [],
            ],
            'get' => [
                'prefilters' => [],
            ],
            'page' => [
                'prefilters' => [],
            ],
        ];
    }

    public function storeAction(int $productID)
    {
        return $this->process(function () use ($productID) {
            $apiResult = service(ApiResult::class);
            $result = $this->favouriteService->add($productID);
            if(!$result) {
                $apiResult->addError(new Error("Ошибка добавления товара в избранное", 'favourite'));
                return $apiResult;
            }
            
            $apiResult->setData([
                'count' => $this->favouriteService->getCountByUser(),
                'items' => $this->favouriteService->getByUser(),
            ]);
            return $apiResult;
        });
    }

    public function deleteAction(int $productID)
    {
        return $this->process(function () use ($productID) {
            $apiResult = service(ApiResult::class);
            $result = $this->favouriteService->deleteByProductID($productID);
            if(!$result) {
                $apiResult->addError(new Error("Ошибка удаления товара из избранного", 'favourite'));
                return $apiResult;
            }
            
            $apiResult->setData([
                'count' => $this->favouriteService->getCountByUser(),
                'items' => $this->favouriteService->getByUser(),
            ]);
            return $apiResult;
        });
    }

    public function toggleAction(int $productID)
    {
        return $this->process(function () use ($productID) {
            $apiResult = service(ApiResult::class);
            $isFavorite = $this->favouriteService->isFavoriteProduct($productID);
            
            if ($isFavorite) {
                $result = $this->favouriteService->deleteByProductID($productID);
                $action = 'removed';
            } else {
                $result = $this->favouriteService->add($productID);
                $action = 'added';
            }
            
            if(!$result) {
                $apiResult->addError(new Error("Ошибка переключения товара в избранном", 'favourite'));
                return $apiResult;
            }
            
            $apiResult->setData([
                'action' => $action,
                'isFavorite' => !$isFavorite,
                'count' => $this->favouriteService->getCountByUser(),
            ]);
            return $apiResult;
        });
    }

    public function clearAction()
    {
        return $this->process(function () {
            $apiResult = service(ApiResult::class);
            $result = $this->favouriteService->clear();
            if(!$result) {
                $apiResult->addError(new Error("Ошибка очистки избранного", 'favourite'));
                return $apiResult;
            }
            
            $apiResult->setData([
                'count' => 0,
                'items' => [],
            ]);
            return $apiResult;
        });
    }

    public function getAction()
    {
        return $this->process(function () {
            $apiResult = service(ApiResult::class);
            
            $apiResult->setData([
                'items' => $this->favouriteService->getByUser(),
                'count' => $this->favouriteService->getCountByUser(),
            ]);
            return $apiResult;
        });
    }

    public function pageAction()
    {
        return $this->process(function () {
            $apiResult = service(ApiResult::class);
            $fileService = service(FileService::class);
            $fileService->includeFile('v1.favourite.index');
            
            return $apiResult;
        });
    }
}
