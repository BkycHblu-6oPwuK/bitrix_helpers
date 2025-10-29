<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

if (empty($arResult))
	return "";

$strReturn = '';

$strReturn .= '<div class="breadcrumbs" itemprop="http://schema.org/breadcrumb" itemscope itemtype="http://schema.org/BreadcrumbList">';
$itemSize = count($arResult);
for ($index = 0; $index < $itemSize; $index++) {
	$title = htmlspecialcharsex($arResult[$index]["TITLE"]);
	if (isLettersUppercase($title) || !containsOnlyLetters($title) || in_array($title, ['Каталог'])) continue;
	$arrow = ($index > 0 ? '<i class="bx-breadcrumb-item-angle"></i>' : '');

	if ($arResult[$index]["LINK"] <> "" && $index != $itemSize - 1) {
		$strReturn .=  $arrow . '
			<div class="breadcrumbs__item" id="bx_breadcrumb_' . $index . '" itemprop="itemListElement" itemscope itemtype="http://schema.org/ListItem">
				<a class="bx-breadcrumb-item-link" href="' . $arResult[$index]["LINK"] . '" title="' . $title . '" itemprop="item">
					<span class="breadcrumbs__item" itemprop="name">' . $title . '</span>
				</a>
				<meta itemprop="position" content="' . ($index + 1) . '" />
			</div>';
	} else {
		$strReturn .= $arrow . '
			<div class="bx-breadcrumb-item">
				<span class="breadcrumbs__item breadcrumbs__item_current">' . $title . '</span>
			</div>';
	}
}

$strReturn .= '</div>';

$strReturn .= '<a href="/" class="return-btn-mobile">
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 16 16" fill="none">
            <path d="M10 12L6 8L10 4" stroke="#6B7280" stroke-width="1.5" stroke-linecap="square" />
        </svg>
        <span>Вернуться на главную</span>
    </a>';

return $strReturn;
