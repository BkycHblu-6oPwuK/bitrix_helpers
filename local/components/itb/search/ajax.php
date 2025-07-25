<?php

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Itb\Catalog\Search;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Компонент поиска
 */
class ItbSearchController extends Controller
{

    /** @inheritDoc */
    public function configureActions()
    {
        return [
            'search' => [
                'prefilters' => [
                    new ActionFilter\Csrf(),
                ],
                'postfilters' => [],
            ],
        ];
    }

    /**
     * Подсказки в поиске
     *
     * @param string $query Поисковый запрос
     * @return AjaxResponse
     */
    public function searchAction($query): array
    {
        $hints = Search::getHints($query);

        if (!empty($hints)) {
            return [
                'success' => true,
                'data' => $hints,
            ];
        } else {
            return [
                'success' => false,
            ];
        }
    }
}
