<?php

use Beeralex\Api\Domain\Iblock\Content\Enum\MainContentTypes;
use Beeralex\Core\Service\IblockService;


foreach ($arResult as $item) {
	switch ($item['TYPE']) {
		case MainContentTypes::SLIDER:
			$APPLICATION->IncludeComponent(
				"beeralex:product.slider",
				".default",
				[
					'IDS' => $item['IDS'],
					'TITLE' => $item['TITLE'],
					'LINK_TO_ALL' => $item['LINK'],
					'TEXT' => $item['TEXT'],
					'IMAGE_SRC' => $item['IMAGE_SRC'],
					"CACHE_TYPE" => "A",
					"CACHE_TIME" => "3600000",
				]
			);
			break;
		case MainContentTypes::MAIN_BANNER:
			$GLOBALS['mainBannerFilter']['=ACTIVE'] = 'Y';
			$APPLICATION->IncludeComponent(
				"bitrix:news.list",
				"mainBanner",
				array(
					"ACTIVE_DATE_FORMAT" => "d.m.Y",
					"ADD_SECTIONS_CHAIN" => "N",
					"AJAX_MODE" => "Y",
					"AJAX_OPTION_ADDITIONAL" => "",
					"AJAX_OPTION_HISTORY" => "N",
					"AJAX_OPTION_JUMP" => "N",
					"AJAX_OPTION_STYLE" => "Y",
					"CACHE_FILTER" => "Y",
					"CACHE_GROUPS" => "Y",
					"CACHE_TIME" => "86400",
					"CACHE_TYPE" => "A",
					"CHECK_DATES" => "Y",
					"COMPONENT_TEMPLATE" => "mainBanner",
					"DETAIL_URL" => "",
					"DISPLAY_BOTTOM_PAGER" => "N",
					"DISPLAY_DATE" => "Y",
					"DISPLAY_NAME" => "Y",
					"DISPLAY_PICTURE" => "Y",
					"DISPLAY_PREVIEW_TEXT" => "Y",
					"DISPLAY_TOP_PAGER" => "N",
					"FIELD_CODE" => array(0 => "ID", 1 => "",),
					"FILTER_NAME" => "mainBannerFilter",
					"HIDE_LINK_WHEN_NO_DETAIL" => "Y",
					"IBLOCK_ID" => service(IblockService::class)->getIblockIdByCode("mainBanner"),
					"IBLOCK_TYPE" => "content",
					"INCLUDE_IBLOCK_INTO_CHAIN" => "N",
					"INCLUDE_SUBSECTIONS" => "Y",
					"MESSAGE_404" => "",
					"NEWS_COUNT" => "20",
					"PAGER_BASE_LINK" => "",
					"PAGER_BASE_LINK_ENABLE" => "Y",
					"PAGER_DESC_NUMBERING" => "N",
					"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
					"PAGER_PARAMS_NAME" => "arrPager",
					"PAGER_SHOW_ALL" => "Y",
					"PAGER_SHOW_ALWAYS" => "N",
					"PAGER_TEMPLATE" => "",
					"PAGER_TITLE" => "Новости",
					"PARENT_SECTION" => "",
					"PARENT_SECTION_CODE" => "",
					"PREVIEW_TRUNCATE_LEN" => "",
					"PROPERTY_CODE" => [
						"LINK",
						"BACKGROUND_TYPE",
						"SHOW_MANUFACTURERS"
					],
					"SET_BROWSER_TITLE" => "N",
					"SET_LAST_MODIFIED" => "N",
					"SET_META_DESCRIPTION" => "N",
					"SET_META_KEYWORDS" => "N",
					"SET_STATUS_404" => "N",
					"SET_TITLE" => "N",
					"SHOW_404" => "N",
					"SORT_BY1" => "ACTIVE_FROM",
					"SORT_BY2" => "SORT",
					"SORT_ORDER1" => "DESC",
					"SORT_ORDER2" => "ASC",
					"STRICT_SECTION_CHECK" => "N",
				)
			);
			break;
		case MainContentTypes::VIDEO:
			$GLOBALS['arrFilterVideo'] = ['ID' => $item['IDS']];
			$APPLICATION->IncludeComponent(
				"bitrix:news.list",
				"video",
				[
					"CACHE_TYPE" => "A",
					"CACHE_TIME" => "3600000",
					"FILTER_NAME" => "arrFilterVideo",
					"IBLOCK_ID" => service(IblockService::class)->getIblockIdByCode('video'),
					"NEWS_COUNT" => "20",
					"SET_BROWSER_TITLE" => "N",
					"SET_LAST_MODIFIED" => "N",
					"SET_META_DESCRIPTION" => "N",
					"SET_META_KEYWORDS" => "N",
					"SET_STATUS_404" => "N",
					"SET_TITLE" => "N",
					"DISPLAY_DATE" => "N",
					"SORT_BY1" => "ID",
					"SORT_ORDER1" => "DESC",
					"PROPERTY_CODE" => [
						"TITLE",
						"VIDEO",
					],
				],
				false
			);
			break;
		case MainContentTypes::ARTICLES:
			$sortBy1 = 'SORT';
			$sortOrder1 = 'ASC';
			if (!empty($item['IDS'])) {
				$GLOBALS['arrFilterArticles'] = ['ID' => $item['IDS']];
			}
			if ($item['TYPE_SLIDER'] === MainContentTypes::SLIDER_NEW) {
				$sortBy1 = 'ID';
				$sortOrder1 = 'DESC';
			}
			$APPLICATION->IncludeComponent(
				"bitrix:news.list",
				"mainArticles",
				[
					"CACHE_TYPE" => "A",
					"CACHE_TIME" => "3600000",
					"FILTER_NAME" => "arrFilterArticles",
					"IBLOCK_ID" => service(IblockService::class)->getIblockIdByCode('articles'),
					"NEWS_COUNT" => "20",
					"SET_BROWSER_TITLE" => "N",
					"SET_LAST_MODIFIED" => "N",
					"SET_META_DESCRIPTION" => "N",
					"SET_META_KEYWORDS" => "N",
					"SET_STATUS_404" => "N",
					"SET_TITLE" => "N",
					"DISPLAY_DATE" => "N",
					"SORT_BY1" => $sortBy1,
					"SORT_ORDER1" => $sortOrder1,
					"LINK_TO_ALL" => $item['LINK']
				],
				false
			);
			break;
		default:
			continue;
	}
}
