<?php

use Bitrix\Main\Engine\Controller;
use Bitrix\Main\Engine\ActionFilter;
use Beeralex\Catalog\Helper\SearchHelper;

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) {
    die();
}

/**
 * Компонент поиска
 */
class BeeralexSearchController extends Controller
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
        $hints = SearchHelper::getHints($query);

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
