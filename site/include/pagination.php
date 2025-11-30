<?php

use Beeralex\Api\Domain\Pagination\PaginationDTO;

if (!$pagination || !($pagination instanceof PaginationDTO)) {
    return;
}
global $APPLICATION;
$baseUrl = $APPLICATION->GetCurPage() . "?{$pagination['paginationUrlParam']}=";
?>
<div>
    <? if (!$arParams['HIDE_SHOW_MODE']): ?>
        <button class="catalog-type__btn-more">Показать еще</button>
    <? endif ?>
    <div class="catalog-type__pagination">
        <? if ($pagination['currentPage'] > 1): ?>
            <a href="<?= $arResult['BASE_URL'] . --$pagination['currentPage'] ?>" class="catalog-type__pagination-prev">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M19 10L1 10" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                    <path d="M6 5L1 10L6 15" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
                <span>Назад</span>
            </a>
            <a href="<?= $arResult['BASE_URL'] . --$pagination['currentPage'] ?>" class="catalog-type__pagination-prev-mobile">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M12.5 15L7.5 10L12.5 5" stroke="#9CA3AF" stroke-width="2" stroke-linecap="square" />
                </svg>
            </a>
        <? endif; ?>
        <div class="catalog-type__pagination-pages">
            <? foreach ($pagination['pages'] as $page): ?>
                <a href="<?= $arResult['BASE_URL'] . $page['pageNumber'] ?>" class="catalog-type__page <?= $page['isSelected'] ? 'active-page' : '' ?>"><?= $page['pageNumber'] ?></a>
            <? endforeach; ?>
        </div>
        <? if ($pagination['currentPage'] < $pagination['pageCount']): ?>
            <a href="<?= $arResult['BASE_URL'] . ++$pagination['currentPage'] ?>" class="catalog-type__pagination-next">
                <span>Дальше</span>
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M1 10L19 10" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                    <path d="M14 5L19 10L14 15" stroke="#6B7280" stroke-width="1.5" stroke-linecap="round"
                        stroke-linejoin="round" />
                </svg>
            </a>
            <a href="<?= $arResult['BASE_URL'] . ++$pagination['currentPage'] ?>" class="catalog-type__pagination-next-mobile">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none">
                    <path d="M7.5 15L12.5 10L7.5 5" stroke="#9CA3AF" stroke-width="2" stroke-linecap="square" />
                </svg>
            </a>
        <? endif; ?>
    </div>
</div>