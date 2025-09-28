<?php

use Itb\Core\Helpers\IblockHelper;
use Itb\Main\Enum\ContentTypes;
dd($arResult);
foreach ($arResult as $item) {
	switch ($item['type']) {
		case ContentTypes::SLIDER:
			$APPLICATION->IncludeComponent(
				"itb:product_slider",
				".default",
				[
					'IDS' => $item['ids'],
					'BIG_ID' => $item['bigId'],
					'TITLE' => $item['title'],
					'LINK_TO_ALL' => $item['link'],
					"CACHE_TYPE" => "A",
					"CACHE_TIME" => "3600000",
				]
			);
			break;
		case ContentTypes::MAIN_BANNER:
			$GLOBALS['mainBannerFilter']['=ID'] = $item['ids'];
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
					"IBLOCK_ID" => IblockHelper::getIblockIdByCode("mainBanner"),
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
					"PROPERTY_CODE" => array(0 => "", 1 => "DESCRIPTION", 2 => "",),
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
					"STRICT_SECTION_CHECK" => "N"
				)
			);
			break;
		case ContentTypes::VIDEO:
			$APPLICATION->IncludeComponent(
				"itb:video",
				".default",
				[
					'VIDEO_LINK' => $item['video_link'],
					'PREVIEW_ID' => $item['video_preview_id'],
					'TEXT' => $item['text'],
					'TITLE' => $item['title'],
					"CACHE_TYPE" => "A",
					"CACHE_TIME" => "3600000",
				]
			);
			break;
		case ContentTypes::TWO_ARTICLES:
			$GLOBALS['arrFilterArticles'] = ['ID' => $item['ids']];
			$APPLICATION->IncludeComponent(
				"bitrix:news.list",
				"two_articles",
				array(
					"IBLOCK_ID" => IblockHelper::getIblockIdByCode("articles"),
					"NEWS_COUNT" => "2",
					"SET_BROWSER_TITLE" => "N",
					"SET_LAST_MODIFIED" => "N",
					"SET_META_DESCRIPTION" => "N",
					"SET_META_KEYWORDS" => "N",
					"SET_STATUS_404" => "N",
					"SET_TITLE" => "N",
					"SHOW_404" => "N",
					"SORT_BY1" => "ID",
					"SORT_ORDER1" => "DESC",
					"FILTER_NAME" => "arrFilterArticles",
					"CACHE_TYPE" => "A",
					"CACHE_TIME" => "3600000",
					"COMPONENT_TEMPLATE" => "two_articles",
					"IBLOCK_TYPE" => "news",
					"SORT_BY2" => "SORT",
					"SORT_ORDER2" => "ASC",
					"FIELD_CODE" => array(
						0 => "PREVIEW_PICTURE",
						1 => "PREVIEW_TEXT",
						2 => "NAME",
					),
					"PROPERTY_CODE" => array(
						0 => "",
					),
					"CHECK_DATES" => "Y",
					"DETAIL_URL" => "",
					"AJAX_MODE" => "N",
					"AJAX_OPTION_JUMP" => "N",
					"AJAX_OPTION_STYLE" => "Y",
					"AJAX_OPTION_HISTORY" => "N",
					"AJAX_OPTION_ADDITIONAL" => "",
					"CACHE_FILTER" => "N",
					"CACHE_GROUPS" => "Y",
					"PREVIEW_TRUNCATE_LEN" => "",
					"ACTIVE_DATE_FORMAT" => "d.m.Y",
					"INCLUDE_IBLOCK_INTO_CHAIN" => "Y",
					"ADD_SECTIONS_CHAIN" => "Y",
					"HIDE_LINK_WHEN_NO_DETAIL" => "N",
					"PARENT_SECTION" => "",
					"PARENT_SECTION_CODE" => "",
					"INCLUDE_SUBSECTIONS" => "Y",
					"STRICT_SECTION_CHECK" => "N",
					"DISPLAY_DATE" => "N",
					"DISPLAY_NAME" => "Y",
					"DISPLAY_PICTURE" => "Y",
					"DISPLAY_PREVIEW_TEXT" => "Y",
					"PAGER_TEMPLATE" => ".default",
					"DISPLAY_TOP_PAGER" => "N",
					"DISPLAY_BOTTOM_PAGER" => "N",
					"PAGER_TITLE" => "Новости",
					"PAGER_SHOW_ALWAYS" => "N",
					"PAGER_DESC_NUMBERING" => "N",
					"PAGER_DESC_NUMBERING_CACHE_TIME" => "36000",
					"PAGER_SHOW_ALL" => "N",
					"PAGER_BASE_LINK_ENABLE" => "N",
					"MESSAGE_404" => ""
				),
				false
			);
			break;
		case ContentTypes::VKONTAKTE:
			require_once $_SERVER['DOCUMENT_ROOT'] . '/include/content/vkontakte.php';
			break;
		case ContentTypes::ARTICLES:
			$GLOBALS['arrFilterArticles'] = ['ID' => $item['ids']];
			$APPLICATION->IncludeComponent(
				"bitrix:news.list",
				"articles",
				[
					"CACHE_TYPE" => "A",
					"CACHE_TIME" => "3600000",
					"FILTER_NAME" => "arrFilterArticles",
					"IBLOCK_ID" => IblockHelper::getIblockIdByCode('articles'),
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
					"LINK_TO_ALL" => $item['link']
				],
				false
			);
			break;
		case ContentTypes::CATALOG_RAZDEL:
			if (empty($item['ids'])) continue;
?>
			<div class="section__container">
				<?
				$APPLICATION->IncludeComponent(
					"itb:main_sections",
					".default",
					[
						'ELEMENT_IDS' => $item['ids'],
						"CACHE_TYPE" => "A",
						"CACHE_TIME" => "3600000",
					]
				);

				?>
			</div>
<?
			break;
		default:
			continue;
	}
}
