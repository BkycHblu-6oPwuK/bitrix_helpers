<?php

namespace Beeralex\Api\V1\Controllers;

use Beeralex\Api\ApiProcessResultTrait;
use Beeralex\Api\ApiResult;
use Beeralex\Core\Http\Controllers\ApiController;
use Beeralex\Core\Service\FileService;
use Beeralex\Reviews\Dto\ReviewDTO;
use Beeralex\Reviews\Services\ReviewsService;

class ReviewController extends ApiController
{
    use ApiProcessResultTrait;

    public function configureActions(): array
    {
        return [
            'index' => [
                'prefilters' => [],
            ],
            'show' => [
                'prefilters' => [],
            ],
            'store' => [
                'prefilters' => [],
            ],
        ];
    }

    public function indexAction()
    {
        $request = $this->getRequest();
        $count = (int)$request->getQuery('count');
        $isFilter = (bool)$request->getQuery('filter');
        $productId = (int)$request->getQuery('product_id');
        $search = (string)$request->getQuery('search');
        return $this->process(function() use ($count, $productId, $search, $isFilter) {
            $result = service(ApiResult::class);
            $fileService = service(FileService::class);
            $fileService->includeFile('v1.reviews.index', [
                'count' => $count ?: 20,
                'productId' => $productId,
                'search' => $search,
                'isFilter' => $isFilter,
            ]);
            $result->setSeo();
            return $result;
        });
    }

    public function storeAction(ReviewDTO $review)
    {
        return $this->process(function() use ($review) {
            $files = $this->getRequest()->getFileList()->toArray();
            $reviewsService = service(ReviewsService::class);
            return $reviewsService->add($review, $files);
        });
    }
}
