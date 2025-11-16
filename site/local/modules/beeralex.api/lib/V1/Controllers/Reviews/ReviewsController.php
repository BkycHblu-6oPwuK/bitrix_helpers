<?php

namespace Beeralex\Reviews\Controllers;

use Bitrix\Main\Engine\ActionFilter\Csrf;
use Bitrix\Main\Web\Json;

class ReviewsController extends BaseController
{
    public function configureActions(): array
    {
        return [
            'add' => [
                'prefilters' => [
                    new Csrf,
                ],
            ],
            'pagination' => [
                'prefilters' => [
                    new Csrf,
                ],
            ],
            'sorting' => [
                'prefilters' => [
                    new Csrf,
                ],
            ],
            'get' => [
                'prefilters' => [
                    new Csrf,
                ],
            ],
        ];
    }

    public function addAction($form)
    {
        try {
            return $this->service->add(Json::decode($form), $_FILES['files'] ?? []);
        } catch (\Exception $e) {
        }
    }

    public function paginationAction($pagination, $sorting)
    {
        try {
            return $this->service->pagination(Json::decode($pagination), Json::decode($sorting));
        } catch (\Exception $e) {
        }
    }

    public function sortingAction($sorting, $pagination)
    {
        try {
            return $this->service->sorting(Json::decode($sorting), Json::decode($pagination));
        } catch (\Exception $e) {
        }
    }

    public function getAction()
    {
        try {
            return $this->service->getReviews();
        } catch (\Exception $e) {
        }
    }
}
