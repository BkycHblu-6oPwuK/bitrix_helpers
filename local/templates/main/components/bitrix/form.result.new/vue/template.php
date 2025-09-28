<?php

use Bitrix\Main\Web\Json;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var array $arResult
 */

?>

<div class="vue-form" data-vue-data="<?=htmlspecialchars(Json::encode($arResult['VUE_DATA']))?>">

</div>