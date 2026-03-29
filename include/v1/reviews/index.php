<?php

use Beeralex\Core\Service\SortingService;
use Beeralex\Reviews\Enum\DIServiceKey;
use Beeralex\Reviews\Repository\ReviewsRepository;
use Bitrix\Main\Loader;

Loader::requireModule('beeralex.reviews');
global $APPLICATION;
$reviewsRepository = service(ReviewsRepository::class);
/**
 * @var SortingService $sortingService
 */
$sortingService = service(DIServiceKey::SORTING_SERVICE->value);
$sorting = $sortingService->getRequestedSort();

if ($isFilter && empty($search)) {
	$GLOBALS['arrFilter'] = [
		'=PROPERTY_PRODUCT' => $productId ?: false,
	];
}


$APPLICATION->IncludeComponent(
	"bitrix:news",
	"reviews",
	array(
		"ADD_ELEMENT_CHAIN" => "N",
		"ADD_SECTIONS_CHAIN" => "Y",
		"AJAX_MODE" => "N",
		"AJAX_OPTION_ADDITIONAL" => "",
		"AJAX_OPTION_HISTORY" => "N",
		"AJAX_OPTION_JUMP" => "N",
		"AJAX_OPTION_STYLE" => "Y",
		"BROWSER_TITLE" => "-",
		"CACHE_FILTER" => "N",
		"CACHE_GROUPS" => "Y",
		"CACHE_TIME" => "36000000",
		"CACHE_TYPE" => "A",
		"CHECK_DATES" => "Y",
		"FILTER_NAME" => "arrFilter",
		"DETAIL_ACTIVE_DATE_FORMAT" => "d.m.Y",
		"DETAIL_DISPLAY_BOTTOM_PAGER" => "Y",
		"DETAIL_DISPLAY_TOP_PAGER" => "N",
		"DETAIL_FIELD_CODE" => array(
			0 => "ID",
			1 => "CODE",
			2 => "NAME",
			3 => "PREVIEW_TEXT",
			4 => "PREVIEW_PICTURE",
			5 => "DETAIL_TEXT",
			6 => "DETAIL_PICTURE",
			7 => "DATE_CREATE",
		),
		"DETAIL_PAGER_SHOW_ALL" => "Y",
		"DETAIL_PAGER_TEMPLATE" => "",
		"DETAIL_PAGER_TITLE" => "Страница",
		"DETAIL_PROPERTY_CODE" => array(
			"USER",
			"EVAL",
			"USER_NAME",
			"FILES",
			"REVIEW",
			"STORE_RESPONSE",
			"CONTACT_DETAILS",
			"PRODUCT",
		),
		"DETAIL_SET_CANONICAL_URL" => "N",
		"DISPLAY_BOTTOM_PAGER" => "Y",
		"DISPLAY_DATE" => "Y",
		"DISPLAY_NAME" => "Y",
		"DISPLAY_PICTURE" => "Y",
		"DISPLAY_PREVIEW_TEXT" => "Y",
		"DISPLAY_TOP_PAGER" => "N",
		"HIDE_LINK_WHEN_NO_DETAIL" => "N",
		"IBLOCK_ID" => $reviewsRepository->entityId,
		"IBLOCK_TYPE" => "catalog",
		"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
		"LIST_ACTIVE_DATE_FORMAT" => "d.m.Y",
		"LIST_FIELD_CODE" => array(
			0 => "ID",
			1 => "CODE",
			2 => "NAME",
			3 => "PREVIEW_TEXT",
			4 => "PREVIEW_PICTURE",
			5 => "DETAIL_TEXT",
			6 => "DETAIL_PICTURE",
			7 => "DATE_CREATE",
			8 => "",
		),
		"LIST_PROPERTY_CODE" => array(
			"USER",
			"EVAL",
			"USER_NAME",
			"FILES",
			"REVIEW",
			"STORE_RESPONSE",
			"CONTACT_DETAILS",
			"PRODUCT",
		),
		"MESSAGE_404" => "",
		"META_DESCRIPTION" => "-",
		"META_KEYWORDS" => "-",
		"NEWS_COUNT" => $count ?? 20,
		"PAGER_BASE_LINK_ENABLE" => "N",
		"PAGER_DESC_NUMBERING" => "N",
		"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
		"PAGER_SHOW_ALL" => "N",
		"PAGER_SHOW_ALWAYS" => "N",
		"PAGER_TEMPLATE" => ".default",
		"PAGER_TITLE" => "Новости",
		"PREVIEW_TRUNCATE_LEN" => "",
		"SEF_MODE" => "Y",
		"SET_LAST_MODIFIED" => "N",
		"SET_STATUS_404" => "N",
		"SET_TITLE" => "Y",
		"SHOW_404" => "N",
		"SORT_BY1" => $sorting['SORT_FIELD_1'],
		"SORT_BY2" => $sorting['SORT_FIELD_2'],
		"SORT_ORDER1" => $sorting['SORT_ORDER_1'],
		"SORT_ORDER2" => $sorting['SORT_ORDER_2'],
		"STRICT_SECTION_CHECK" => "N",
		"USE_CATEGORIES" => "N",
		"USE_FILTER" => "Y",
		"USE_PERMISSIONS" => "N",
		"USE_RATING" => "N",
		"USE_REVIEW" => "N",
		"USE_RSS" => "N",
		"USE_SEARCH" => "N",
		"USE_SHARE" => "N",
		"SEF_FOLDER" => "/",
		"SEF_URL_TEMPLATES" => array(
			"news" => "api/v1/reviews",
			"section" => "",
			"detail" => "api/v1/reviews/#ELEMENT_ID#",
		)
	),
	false
);
